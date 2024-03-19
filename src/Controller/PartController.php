<?php

namespace App\Controller;

use App\Entity\Part;
use App\Entity\Stock;
use App\Form\PartType;
use App\Data\SearchPart;
use App\Service\PdfService;
use App\Form\SearchPartForm;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use App\Repository\StockValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security as Secu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/part")
 */
class PartController extends AbstractController
{
    protected $partRepository;
    protected $stockValueRepository;
    protected $manager;
    protected $requestStack;
    protected $deliveryNoteRepository;
    protected $security;
    protected $organisation;


    public function __construct(
        OrganisationService $organisation,
        Secu $security,
        DeliveryNoteRepository $deliveryNoteRepository,
        PartRepository $partRepository,
        StockValueRepository $stockValueRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack
    ) {
        $this->partRepository = $partRepository;
        $this->stockValueRepository = $stockValueRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->deliveryNoteRepository = $deliveryNoteRepository;
        $this->security = $security;
        $this->organisation = $organisation;
    }


    /**
     * @ Liste des pièces détachées
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/list/{mode?}/{documentId?}', name: 'part_index', methods: ('GET'))]
    public function index(
        Request $request,
        ?string $mode = null,
        ?int $documentId = null
    ): Response {
        $organisation =  $this->organisation->getOrganisation();

        $session = $this->requestStack->getSession();

        $data = new SearchPart();

        if (
            $mode == "workorderAddPart"
            || $mode == "newDeliveryNote"
            || $mode == "editDeliveryNote"
        ) {
            $data = $session->get('data', null);
            if ($data === null) {
                $data = new SearchPart();
            }
        }

        $data->organisation = $organisation;

        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchPartForm::class, $data);

        $form->handleRequest($request);

        $session->set('data', $data); // Sauvegarde de la recherche
        $parts = $this->partRepository->findSearch($data);

        if ($request->get('ajax')) {
            return new JsonResponse(
                [
                    'content'       =>  $this->renderView(
                        'part/_parts.html.twig',
                        [
                            'parts' => $parts,
                            'mode' => $mode,
                            'documentId' => $documentId
                        ]
                    ),
                    'sorting'       =>  $this->renderView(
                        'part/_sorting.html.twig',
                        ['parts' => $parts]
                    ),
                    'pagination'    =>  $this->renderView(
                        'part/_pagination.html.twig',
                        ['parts' => $parts]
                    ),
                ]
            );
        }

        if (
            $mode == 'workorderAddPart'
            || $mode == 'editReceivedPart'
            || $mode == 'editDeliveryNote'
            || $mode == 'newDeliveryNote'
        ) {
            $panier = $session->get('panier', null);
            if ($panier == true) {
                $panierWithData = [];
                foreach ($panier as $id => $quantity) {
                    $panierWithData[] = [
                        'part' => $this->partRepository->find($id),
                        'quantity' => $quantity,
                    ];
                }
                return $this->render(
                    'part/index.html.twig',
                    [
                        'parts' =>  $parts,
                        'form'  =>  $form->createView(),
                        'mode'  =>  $mode,
                        'items' => $panierWithData,
                        'documentId' => $documentId,
                    ]
                );
            }
            return $this->render(
                'part/index.html.twig',
                [
                    'parts' =>  $parts,
                    'form'  =>  $form->createView(),
                    'documentId' => $documentId,
                    'mode'  =>  $mode,
                ]
            );
        }
        return $this->render(
            'part/index.html.twig',
            [
                'parts' =>  $parts,
                'form'  =>  $form->createView(),
                'documentId' => $documentId,
                'mode'  =>  "index",
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/ajaxPartsList', name: 'ajax_parts_list', methods: ["GET"])]
    public function ajaxListParts(): JsonResponse
    {
        $organisation =  $this->organisation->getOrganisation();

        $parts = $this->partRepository->findParts($organisation);

        $partsArray= array_map(function($part){
            return [
                'id' => $part->getId(),
                'code' => $part->getCode(),
                'designation' => $part->getDesignation(),
                'reference' => $part->getReference()
            ];
        }, $parts);

        return $this->json($partsArray);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/ajaxPart/{code}', name: 'ajax_part', methods: ["GET"])]
    public function ajaxPart(string $code): JsonResponse
    {
        $organisationId =  $this->organisation->getOrganisation()->getId();

        $part = $this->partRepository->findPartByCode($organisationId, $code);
        $part=[ 
            'id' => $part->getId(),   
            'code' => $part->getCode(),
            'designation' => $part->getDesignation(),
            'reference' => $part->getReference(),
            'qteMax' => $part->getStock()->getQteMax(),
            'qteStock' => $part->getStock()->getQteStock(),
            'price' => $part->getSteadyPrice()
        ];

        return $this->json($part);
    }

    /**
     * @ Nouvelle pièce détachée
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'part_new', methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $organisation =  $this->organisation->getOrganisation();
        $part = new Part();
        $stock = new Stock();
        $stock->setApproQte(0);
        $part->setStock($stock);

        $form = $this->createForm(PartType::class, $part);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $part->setActive(true);
            $part->setOrganisation($organisation);
            $part->setCode(strtoupper($part->getCode()));
            $part->setReference(strtoupper($part->getReference()));
            $part->setDesignation(ucfirst($part->getDesignation()));
            $part->getStock()->setPlace(strtoupper($part->getStock()->getPlace()));


            $this->manager->persist($part);
            $this->manager->flush();

            return $this->redirectToRoute(
                'part_show',
                ['id' => $part->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'part/new.html.twig',
            [
                'part' => $part,
                'form' => $form,
            ]
        );
    }

    /**
     * @ Visualisation pièce
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/show/{id}', name: 'part_show', methods: ["GET"])]
    public function show(Part $part): Response
    {
        return $this->render(
            'part/show.html.twig',
            ['part' => $part]
        );
    }

    /**
     * @ Edition pièce
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/edit/{id}', name: 'part_edit', methods: ["GET", "POST"])]
    public function edit(Request $request, Part $part): Response
    {
        $form = $this->createForm(PartType::class, $part);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $part->setCode(strtoupper($part->getCode()));
            $part->setReference(strtoupper($part->getReference()));
            $part->setDesignation(strtoupper($part->getDesignation()));
            $part->getStock()->setPlace(strtoupper($part->getStock()->getPlace()));

            $this->manager->flush();

            return $this->redirectToRoute(
                'part_show',
                [
                    'id' => $part->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'part/edit.html.twig',
            [
                'part' => $part,
                'form' => $form,
            ]
        );
    }

    /**
     * @ Désactiver pièce détachée
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/delete/{id}', name: 'part_delete', methods: ["POST"])]
    public function delete(Request $request, Part $part): Response
    {
        if ($this->isCsrfTokenValid(
            'delete' . $part->getId(),
            $request->request->get('_token')
        )) {
            $part->setActive(false);
            $this->manager->flush();
        }

        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @ Mise à zéro de la quantité d'approvisionnement
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/approzero', name: 'appro_to_zero')]
    public function setApproToZero(): Response
    {
        $parts = $this->partRepository->findAll();

        foreach ($parts as $part) {
            $approQte = $part->getStock()->getApproQte();
            if ($approQte === null) {
                $part->getStock()->setApproQte(0);
                $this->manager->persist($part);
            }
            if ($part->getActive() === false) {
                $part->setActive(true);
            }
        }
        $this->manager->flush();

        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @ Liste des pièces détachaes
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/reception', name: 'reception_part')]
    public function reception(): Response
    {
        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @ Valeur du stock de pièces détachées
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/infos', name: 'parts_value', methods: ['GET', 'POST'])]
    public function infos(): Response
    {
        $organisation =  $this->organisation->getOrganisation();

        $totalStock = $this->partRepository->findTotalStock($organisation);
        $stockValues = $this->stockValueRepository->findStockValues($organisation);

        $amounts = [];
        $dates = [];

        foreach ($stockValues as $value) {
            $amount = $value->getValue();
            $amounts[] = $amount;
            $date =  $value->getDate()->format('d/m/Y');
            $dates[] = $date;
        }

        return $this->render(
            'part/infos_pieces.html.twig',
            [
                'totalStock' => $totalStock,
                'dates' =>  json_encode($dates),
                'amounts' => json_encode($amounts),
            ]
        );
    }

    /**
     * @ Affichage des bons de livraison liés à une pièce
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/appro/{id}', name: 'show_appro')]
    public function appro(Part $part)
    {
        $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNotesAppro(
            $part->getId()
        );

        return $this->render(
            'part/showAppro.html.twig',
            [
                'deliveryNotes' => $deliveryNotes,
                'part' => $part,
            ]
        );
    }

    /**
     * Impession étiquette d'une pièce 
     * 
     * @param PdfService $pdfService
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/partlabel/{id}', name: 'part_label')]
    public function printLabel(Part $part, PdfService $pdfService): Response
    {
        $html = $this->renderView(
            'prints/one_label_print.html.twig',
            ['part' => $part]
        );

        $pdfService->printLabel($html);

        return new Response(
            '',
            200,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * @ Valeur du stock de pièces détachées + affichage des 50 pièces les + chères
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/stockTopValue', name: 'stock_top_value', methods: ["GET", "POST"])]
    public function topStockValue(): Response
    {
        $organisation =  $this->organisation->getOrganisation();

        $totalStock = $this->partRepository->findTotalStock($organisation);
        $stockValues = $this->stockValueRepository->findStockValues($organisation);
        $topParts = $this->partRepository->findTopValueParts($organisation);
        // dd($topParts);

        $amounts = [];
        $dates = [];

        foreach ($stockValues as $value) {
            $amount = $value->getValue();
            $amounts[] = $amount;
            $date =  $value->getDate()->format('d/m/Y');
            $dates[] = $date;
        }

        return $this->render(
            'part/top_value_parts.html.twig',
            [
                'totalStock' => $totalStock,
                'dates' =>  json_encode($dates),
                'amounts' => json_encode($amounts),
                'parts' => $topParts,
            ]
        );
    }
}
