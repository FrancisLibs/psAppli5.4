<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\DeliveryNote;
use App\Form\DeliveryNoteType;
use App\Data\SearchDeliveryNote;
use App\Entity\DeliveryNotePart;
use App\Repository\PartRepository;
use App\Form\SearchDeliveryNoteForm;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/delivery/note")
 */
class DeliveryNoteController extends AbstractController
{
    private $manager;
    private $requestStack;
    private $deliveryNoteRepository;
    private $providerRepository;
    private $partRepository;

    public function __construct(
        EntityManagerInterface $entityManagerInterface,
        RequestStack $requestStack,
        DeliveryNoteRepository $deliveryNoteRepository,
        ProviderRepository $providerRepository,
        PartRepository $partRepository
    ) {
        $this->manager = $entityManagerInterface;
        $this->requestStack = $requestStack;
        $this->deliveryNoteRepository = $deliveryNoteRepository;
        $this->providerRepository = $providerRepository;
        $this->partRepository = $partRepository;
    }

    /**
     * Liste des BL
     * @Route("/", name="delivery_note_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(Request $request, DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        $data = new SearchDeliveryNote();

        // Effacement du fournisseur, des pièces détachées, de la date et fournisseur en session
        $session = $this->requestStack->getSession();
        $session->remove('providerId');
        $session->remove('panier');
        $session->remove('deliveryNoteDate');
        $session->remove('deliveryNoteNumber');

        $organisation = $this->getUser()->getOrganisation();
        $data->organisation = $organisation;

        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchDeliveryNoteForm::class, $data);
        $form->handleRequest($request);
        $deliveryNotes = $this->deliveryNoteRepository->findSearch($data);

        return $this->render('delivery_note/index.html.twig', [
            'delivery_notes' => $deliveryNotes,
            'form'          =>  $form->createView(),
        ]);
    }

    /**
     * @Route("/new/{providerId?}", name="delivery_note_new", methods={"GET","POST"})
     */
    public function new(Request $request, int $providerId = null): Response
    {
        $user = $this->getUser();
        $session = $this->requestStack->getSession();
        $organisation = $user->getOrganisation();
        $deliveryNote = new DeliveryNote();

        // Gestion du fournisseur du BL en session
        if ($providerId) {
            $session->set('providerId', $providerId);
            $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        } else {
            $providerId = $session->get('providerId', null);
            if ($providerId) {
                $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
            }
        }

        // Gestion du numéro du BL en session
        $deliveryNoteNumber = $session->get('deliveryNoteNumber', null);
        if ($deliveryNoteNumber) {
            $deliveryNote->setNumber($deliveryNoteNumber);
        }

        // Gestion de la date du BL en session
        $deliveryNoteDate = $session->get('deliveryNoteDate', null);
        if ($deliveryNoteDate) {
            $deliveryNoteDate = new \DateTime($deliveryNoteDate);
            $deliveryNote->setDate($deliveryNoteDate);
        }

        // Gestion des pièces en session
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

            // Modification du stock de pièces détachées et du nombre de pièces en commande
            $deliveryNoteParts = $deliveryNote->getDeliveryNoteParts();
            foreach ($deliveryNoteParts as $deliveryNotePart) {
                $deliveryNotePartQte = $deliveryNotePart->getQuantity();

                $partStockQte = $deliveryNotePart->getPart()->getStock()->getQteStock();

                $deliveryNotePart->getPart()->getStock()->setQteStock($deliveryNotePartQte + $partStockQte);

                $partsInOrder = $deliveryNotePart->getPart()->getStock()->getApproQte();

                if ($partsInOrder >= $deliveryNotePartQte) {
                    $deliveryNotePart->getPart()->getStock()->setApproQte($partsInOrder - $deliveryNotePartQte);
                }
            }

            $this->manager->persist($deliveryNote);
            $this->manager->flush();

            // Effacement du panier de pièces détachées
            $session->remove('panier');

            return $this->redirectToRoute('delivery_note_show', [
                'id' => $deliveryNote->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        if (isset($provider)) {
            return $this->renderForm('delivery_note/new.html.twig', [
                'form' => $form,
                'provider' => $provider,
                'mode' => 'newDeliveryNote',
            ]);
        }

        return $this->renderForm('delivery_note/new.html.twig', [
            'deliveryNote' => $deliveryNote,
            'form' => $form,
            'mode' => 'newDeliveryNote',
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_note_show", methods={"GET"})
     */
    public function show(DeliveryNote $deliveryNote): Response
    {
        return $this->render('delivery_note/show.html.twig', [
            'delivery_note' => $deliveryNote,
        ]);
    }

    /**
     * @Route("/{id}/edit/{providerId?}", name="delivery_note_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DeliveryNote $deliveryNote, ?int $providerId = null): Response
    {
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

            // Après traitement -> suppression du panier
            $session->remove('panier');
        }

        // Mise en mémoire des pièces du BL avant modification, pour le traitement des pièces détachées
        $oldParts = $session->get('oldParts', null);
        if (!$oldParts) {
            $oldParts = $deliveryNote->getDeliveryNoteParts();
            $session->set('oldParts', $oldParts);
        }

        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1) Lire les pièces contenues dans le formulaire
            $parts = $deliveryNote->getDeliveryNoteParts();

            // 2) Boucler sur les pièces de l'ancien BL et comparer avec les pièces du formulaire
            foreach ($parts as $part) {
                $flag = true;
                $id = $part->getPart()->getId();
                // Quantité actuelle en stock pour la pièce en cours
                $qteStock = $part->getPart()->getStock()->getQteStock();

                foreach ($oldParts as $oldPart) {
                    $oldId = $oldPart->getPart()->getId();

                    // Si c'est la même pièce
                    if ($id == $oldId) {
                        // $qte est la différence de quantité entre l'ancien BL et celui modifié
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
                // C'est une nouvelle pièce
                if ($flag) {
                    $qteStock = $part->getPart()->getStock()->getQteStock();
                    $qte = $part->getQuantity();
                    $part->getPart()->getStock()->setQteStock($qte + $qteStock);
                }
            }
            $session->remove('oldParts');
            $this->manager->flush();


            return $this->redirectToRoute('delivery_note_show', [
                'id' => $deliveryNote->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('delivery_note/edit.html.twig', [
            'deliveryNote' => $deliveryNote,
            'form' => $form,
            'mode' => 'editDeliveryNote'
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_note_delete", methods={"POST"})
     */
    public function delete(Request $request, DeliveryNote $deliveryNote): Response
    {
        if ($this->isCsrfTokenValid('delete' . $deliveryNote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryNote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('delivery_note_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/removePart/{partId}", name="delivery_note_delete_part", methods={"GET"})
     */
    public function removePart(Request $request, DeliveryNote $deliveryNote, int $partId): Response
    {
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

        return $this->redirectToRoute('delivery_note_show', [
            'id' => $deliveryNote->getId()
        ], Response::HTTP_SEE_OTHER);
    }

    /**
     * Mise en session du numéro du BL
     *
     * @Route("/saveNumber/{deliveryNoteNumber?}", name="save_number_in_session", methods={"GET"})
     * 
     * @param string $deliveryNoteNumber
     * @return void
     */
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
     *
     * @Route("/saveDate/{deliveryNoteDate?}", name="save_date_in_session", methods={"GET"})
     * 
     * @param string $deliveryNoteNumber
     * @return void
     */
    public function deliveryNoteDateSession(?string $deliveryNoteDate)
    {
        $session = $this->requestStack->getSession();
        $session->set('deliveryNoteDate', $deliveryNoteDate);
        return $this->json(['code' => 200, 'message' => $deliveryNoteDate], 200);
    }

    /**
     * Impression d'étiquettes de pièces à la réception
     * 
     * @Route("/label/{id}", name="label_print")
     * @Security("is_granted('ROLE_USER')")
     * 
     */
    public function pdfAction(DeliveryNote $deliveryNote)
    {
        $deliveryNoteParts = $deliveryNote->getDeliveryNoteParts();
        // Retrieve the HTML generated in our twig file

        $html = $this->renderView('delivery_note/label_print.html.twig', [
            'deliveryNoteParts' => $deliveryNoteParts
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Courier');
        $options->setDefaultMediaType('print');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($options);

        $customPaper = array(0, 0, 200, 100);
        $dompdf->setPaper($customPaper);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Render the HTML as PDF
        $dompdf->render();
        //dd($dompdf);

        // Output the generated PDF to Browser (force download)
        $dompdf->stream();

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
