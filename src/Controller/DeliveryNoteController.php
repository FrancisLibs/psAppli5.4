<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\PdfService;
use App\Entity\DeliveryNote;
use App\Form\DeliveryNoteType;
use App\Data\SearchDeliveryNote;
use App\Entity\DeliveryNotePart;
use App\Repository\PartRepository;
use App\Form\SearchDeliveryNoteForm;
use App\Service\OrganisationService;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/delivery/note")
 */

class DeliveryNoteController extends AbstractController
{

    protected $manager;

    protected $requestStack;

    protected $deliveryNoteRepo;

    protected $providerRepository;

    protected $partRepository;
    
    protected $organisation;


    public function __construct(
        OrganisationService $organisation,
        EntityManagerInterface $entityManagerInterface,
        RequestStack $requestStack,
        DeliveryNoteRepository $deliveryNoteRepo,
        ProviderRepository $providerRepository,
        PartRepository $partRepository
    ) {
        $this->manager = $entityManagerInterface;
        $this->requestStack = $requestStack;
        $this->deliveryNoteRepo = $deliveryNoteRepo;
        $this->providerRepository = $providerRepository;
        $this->partRepository = $partRepository;
        $this->organisation = $organisation;
    }


    /**
     * Liste des BL
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'delivery_note_index', methods: ["GET"])]
    public function index(Request $request): Response
    {
        $organisation = $this->organisation->getOrganisation();

        $data = new SearchDeliveryNote();
        $data->organisation = $organisation;
        $data->page = $request->get('page', 1);

        // Effacement du fournisseur, des pièces détachées.
        // de la date et fournisseur en session.
        $session = $this->requestStack->getSession();
        $session->remove('providerId');
        $session->remove('panier');
        $session->remove('deliveryNoteDate');
        $session->remove('deliveryNoteNumber');

        $form = $this->createForm(SearchDeliveryNoteForm::class, $data);

        $form->handleRequest($request);

        $deliveryNotes = $this->deliveryNoteRepo->findSearch($data);

        if ($request->get('ajax') === true) {
            return new JsonResponse(
                [
                    'content' => $this->renderView(
                        'delivery_note/_delivery_notes.html.twig',
                        ['delivery_notes' => $deliveryNotes]
                    ),
                    'sorting' => $this->renderView(
                        'delivery_note/_sorting.html.twig',
                        ['delivery_notes' => $deliveryNotes]
                    ),
                    'pagination' => $this->renderView(
                        'delivery_note/_pagination.html.twig',
                        ['delivery_notes' => $deliveryNotes]
                    ),
                ]
            );
        }

        return $this->render(
            'delivery_note/index.html.twig',
            [
                'delivery_notes' => $deliveryNotes,
                'form' =>  $form->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'delivery_note_new', methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $session = $this->requestStack->getSession();
        $organisation =  $this->organisation->getOrganisation();
        $deliveryNote = new DeliveryNote();

        // Vérification si fournisseur du BL en session.
        $providerId = $session->get('providerId', null);
        if ($providerId) {
            $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        }

        // Gestion du numéro du BL en session.
        $deliveryNoteNumber = $session->get('deliveryNoteNumber', null);
        if ($deliveryNoteNumber) {
            $deliveryNote->setNumber($deliveryNoteNumber);
        }

        // Gestion de la date du BL en session.
        $deliveryNoteDate = $session->get('deliveryNoteDate', null);
        if ($deliveryNoteDate) {
            $deliveryNote->setDate(new \DateTime($deliveryNoteDate));
        }

        // Gestion des pièces en session.
        $panier = $session->get('panier', []);
        if ($panier) {
            foreach ($panier as $id => $quantity) {
                $deliveryNotePart = new DeliveryNotePart();
                $part = $this->partRepository->find($id);
                $deliveryNotePart->setPart($part);
                $deliveryNotePart->setQuantity($quantity);
                $deliveryNote->addDeliveryNotePart($deliveryNotePart);
            }
            $this->manager->persist($deliveryNotePart);
        }

        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (empty($provider)) {
                $this->addFlash('error', 'Tu n\'as pas sélectionné de fournisseur');
                return $this->redirectToRoute('delivery_note_new');
            }

            $deliveryNote->setProvider($provider);
            $deliveryNote->setOrganisation($organisation);
            $deliveryNote->setUser($user);

            $this->manager->persist($deliveryNote);

            // Modification du stock de pièces détachées.
            // et du nombre de pièces en commande.
            $deliveryNoteParts = $deliveryNote->getDeliveryNoteParts();
            foreach ($deliveryNoteParts as $deliveryNotePart) {
                $deliveryNotePartQte = $deliveryNotePart->getQuantity();
                $partStockQte = $deliveryNotePart
                    ->getPart()
                    ->getStock()
                    ->getQteStock();
                $deliveryNotePart
                    ->getPart()
                    ->getStock()
                    ->setQteStock($deliveryNotePartQte + $partStockQte);
                $partsInOrder = $deliveryNotePart
                    ->getPart()
                    ->getStock()
                    ->getApproQte();
                if ($partsInOrder >= $deliveryNotePartQte) {
                    $deliveryNotePart
                        ->getPart()
                        ->getStock()
                        ->setApproQte($partsInOrder - $deliveryNotePartQte);
                }
            }

            $this->manager->persist($deliveryNote);
            $this->manager->flush();

            // Effacement du panier de pièces détachées.
            $session->remove('panier');

            return $this->redirectToRoute(
                'delivery_note_show',
                [
                    'id' => $deliveryNote->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        if (isset($provider)) {
            return $this->renderForm(
                'delivery_note/new.html.twig',
                [
                    'form' => $form,
                    'provider' => $provider,
                    'mode' => 'newDeliveryNote',
                ]
            );
        }

        return $this->renderForm(
            'delivery_note/new.html.twig',
            [
                'deliveryNote' => $deliveryNote,
                'form' => $form,
                'mode' => 'newDeliveryNote',
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/{id}',
        name: 'delivery_note_show',
        methods: ["GET"]
    )]
    public function show(DeliveryNote $deliveryNote): Response
    {
        return $this->render(
            'delivery_note/show.html.twig',
            [
                'delivery_note' => $deliveryNote,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/{id}/edit/{providerId?}',
        name: 'delivery_note_edit',
        methods: ["GET", "POST"]
    )]
    public function edit(
        Request $request,
        DeliveryNote $deliveryNote,
        ?int $providerId = null
    ): Response {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if ($providerId) {
            $provider = $this->providerRepository->findOneById($providerId);
            $deliveryNote->setProvider($provider);
            $this->manager->flush();
        }

        // Traitement des pièces du panier
        $parts = $deliveryNote->getDeliveryNoteParts();
        if ($panier) {
            foreach ($panier as $id => $qte) {
                $flag = true;
                foreach ($parts as $part) {
                    if ($id == $part->getPart()->getId()) {
                        $part->setQuantity($part->getQuantity() + $qte);
                        $flag = false;
                    }
                }
            }
            if ($flag) {
                $deliveryNotePart = new DeliveryNotePart();
                $part = $this->partRepository->find($id);
                $deliveryNotePart->setPart($part);
                $deliveryNotePart->setQuantity($qte);
                $deliveryNote->addDeliveryNotePart($deliveryNotePart);
            }
            $this->manager->flush();

            // Après traitement -> suppression du panier.
            $session->remove('panier');
        }

        // Mise en mémoire des pièces du BL avant modification.
        // pour le traitement des pièces détachées.
        $oldParts = $session->get('oldParts', null);
        if (!$oldParts) {
            $oldParts = $deliveryNote->getDeliveryNoteParts();
            $session->set('oldParts', $oldParts);
        }

        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1) Lire les pièces contenues dans le formulaire.
            $parts = $deliveryNote->getDeliveryNoteParts();

            // 2) Boucler sur les pièces de l'ancien BL et comp avec les pièces du form.
            foreach ($parts as $part) {
                $flag = true;
                $id = $part->getPart()->getId();
                // Quantité actuelle en stock pour la pièce en cours.
                $qteStock = $part->getPart()->getStock()->getQteStock();

                foreach ($oldParts as $oldPart) {
                    $oldId = $oldPart->getPart()->getId();

                    // Si c'est la même pièce.
                    if ($id == $oldId) {
                        // $qte est la différence de quantité entre.
                        // l'ancien BL et celui modifié.
                        $qte = $part->getQuantity() - $oldPart->getQuantity();
                        $part->getPart()->getStock()->setQteStock($qteStock + $qte);
                        if ($part->getQuantity() == 0) {
                            $deliveryNote->removeDeliveryNotePart($part);
                            $this->manager->remove($part);
                            $this->manager->flush();
                        }
                        $flag = false;
                    }
                }
                // C'est une nouvelle pièce.
                if ($flag) {
                    $qteStock = $part->getPart()->getStock()->getQteStock();
                    $qte = $part->getQuantity();
                    $part->getPart()->getStock()->setQteStock($qte + $qteStock);
                }
            }
            $session->remove('oldParts');
            $this->manager->flush();


            return $this->redirectToRoute(
                'delivery_note_show',
                [
                    'id' => $deliveryNote->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'delivery_note/edit.html.twig',
            [
                'deliveryNote' => $deliveryNote,
                'form' => $form,
                'mode' => 'editDeliveryNote'
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'delivery_note_delete', methods: ["POST"])]
    public function delete(Request $request, DeliveryNote $deliveryNote): Response
    {
        if ($this->isCsrfTokenValid(
            'delete' . $deliveryNote->getId(),
            $request->request->get('_token')
        )
        ) {
            $this->manager->remove($deliveryNote);
            $this->manager->flush();
        }

        return $this->redirectToRoute(
            'delivery_note_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/{id}/removePart/{partId}',
        name: 'delivery_note_delete_part',
        methods: ["GET"]
    )]
    public function removePart(
        Request $request,
        DeliveryNote $deliveryNote,
        int $partId
    ): Response {
        $deliveryNoteParts = $deliveryNote->getDeliveryNoteParts();

        foreach ($deliveryNoteParts as $part) {
            $id = $part->getPart()->getId();
            if ($id == $partId) {
                if ($part->getQuantity() > 1) {
                    $part->setQuantity($part->getQuantity() - 1);
                } else {
                    $deliveryNote->removeDeliveryNotePart($part);
                }
                $this->manager->persist($part);
            }
        }
        $this->manager->persist($deliveryNote);
        $this->manager->flush();

        return $this->redirectToRoute(
            'delivery_note_show',
            [
                'id' => $deliveryNote->getId()
            ],
            Response::HTTP_SEE_OTHER
        );
    }

    /**
     * Mise en session du numéro du BL
     */
    #[Route(
        '/saveNumber/{deliveryNoteNumber?}',
        name: 'save_number_in_session',
        methods: ["GET"]
    )]
    public function deliveryNoteNumberSession(?string $deliveryNoteNumber)
    {
        $session = $this->requestStack->getSession();
        if ($deliveryNoteNumber === null) {
            $session->remove('deliveryNoteNumber');
        } else {
            $session->set('deliveryNoteNumber', $deliveryNoteNumber);
        }
        return $this->json(['code' => 200, 'message' => $deliveryNoteNumber], 200);
    }

    /**
     * Mise en session de la date du BL
     */
    #[Route(
        '/saveDate/{deliveryNoteDate?}',
        name: 'save_date_in_session',
        methods: ["GET"]
    )]
    public function deliveryNoteDateSession(?string $deliveryNoteDate)
    {
        $session = $this->requestStack->getSession();
        $session->set('deliveryNoteDate', $deliveryNoteDate);
        return $this->json(['code' => 200, 'message' => $deliveryNoteDate], 200);
    }

    /**
     * Impression d'étiquettes de pièces à la réception
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/label/{id}',
        name: 'label_print',
        methods: ["GET"]
    )]
    public function printLabel(
        DeliveryNote $deliveryNote,
        PdfService $pdfService
    ): Response {
        $deliveryNoteParts = $deliveryNote->getDeliveryNoteParts();

        $html = $this->renderView(
            'prints/multi_label_print.html.twig',
            ['deliveryNoteParts' => $deliveryNoteParts]
        );

        $pdfService->printLabel($html);

        return new Response(
            '',
            200,
            ['Content-Type' => 'application/pdf']
        );
    }
}
