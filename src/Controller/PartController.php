<?php

namespace App\Controller;

use App\Entity\Part;
use App\Entity\Stock;
use App\Form\PartType;
use App\Data\SearchPart;
use App\Form\SearchPartForm;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use App\Repository\StockValueRepository;
use App\Repository\DeliveryNoteRepository;
use App\Service\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/part")
 */
class PartController extends AbstractController
{
    protected PartRepository $partRepository;
    protected StockValueRepository $stockValueRepository;
    protected EntityManagerInterface $manager;
    protected RequestStack $requestStack;
    protected DeliveryNoteRepository $deliveryNoteRepository;
    protected OrganisationService $organisation;

    public function __construct(
        OrganisationService $organisation,
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
        $this->organisation = $organisation;
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/list/{mode?}/{documentId?}', name: 'part_index', methods: ['GET'])]
    public function index(Request $request, ?string $mode = null, ?int $documentId = null): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $session = $this->requestStack->getSession();

        $data = $session->get('data', new SearchPart());
        $data->organisation = $organisation;
        $data->page = $request->get('page', 1);

        $form = $this->createForm(SearchPartForm::class, $data);
        $form->handleRequest($request);

        $session->set('data', $data);
        $parts = $this->partRepository->findSearch($data);

        if ($request->get('ajax')) {
            return new JsonResponse(
                [
                'content' => $this->renderView('part/_parts.html.twig', ['parts' => $parts, 'mode' => $mode, 'documentId' => $documentId]),
                'sorting' => $this->renderView('part/_sorting.html.twig', ['parts' => $parts]),
                'pagination' => $this->renderView('part/_pagination.html.twig', ['parts' => $parts]),
                ]
            );
        }

        return $this->render(
            'part/index.html.twig', [
            'parts' => $parts,
            'form' => $form->createView(),
            'mode' => $mode ?? 'index',
            'documentId' => $documentId,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/ajaxPartsList', name: 'ajax_parts_list', methods: ["GET"])]
    public function ajaxListParts(): JsonResponse
    {
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findParts($organisation);

        $partsArray = array_map(
            fn($part) => [
            'id' => $part->getId(),
            'code' => $part->getCode(),
            'designation' => $part->getDesignation(),
            'reference' => $part->getReference()
            ], $parts
        );

        return $this->json($partsArray);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/ajaxPart/{code}', name: 'ajax_part', methods: ["GET"])]
    public function ajaxPart(string $code): JsonResponse
    {
        $organisationId = $this->organisation->getOrganisation()->getId();
        $part = $this->partRepository->findPartByCode($organisationId, $code);

        return $this->json(
            [
            'id' => $part->getId(),
            'code' => $part->getCode(),
            'designation' => $part->getDesignation(),
            'reference' => $part->getReference(),
            'qteMax' => $part->getStock()->getQteMax(),
            'qteStock' => $part->getStock()->getQteStock(),
            'price' => $part->getSteadyPrice(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'part_new', methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $organisation = $this->organisation->getOrganisation();
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

            return $this->redirectToRoute('part_show', ['id' => $part->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('part/new.html.twig', ['part' => $part, 'form' => $form]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/show/{id}', name: 'part_show', methods: ["GET"])]
    public function show(Part $part): Response
    {
        return $this->render('part/show.html.twig', ['part' => $part]);
    }

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

            return $this->redirectToRoute('part_show', ['id' => $part->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('part/edit.html.twig', ['part' => $part, 'form' => $form]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete/{id}', name: 'part_delete', methods: ["POST"])]
    public function delete(Request $request, Part $part): Response
    {
        if ($this->isCsrfTokenValid('delete'.$part->getId(), $request->request->get('_token'))) {
            $part->setActive(false);
            $this->manager->flush();
        }
        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/approzero', name: 'appro_to_zero')]
    public function setApproToZero(): Response
    {
        $parts = $this->partRepository->findAll();
        foreach ($parts as $part) {
            if ($part->getStock()->getApproQte() === null) {
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

    #[IsGranted('ROLE_USER')]
    #[Route('/reception', name: 'reception_part')]
    public function reception(): Response
    {
        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/infos', name: 'parts_value', methods: ['GET', 'POST'])]
    public function infos(): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $totalStock = $this->partRepository->findTotalStock($organisation);
        $stockValues = $this->stockValueRepository->findStockValues($organisation);

        $amounts = [];
        $dates = [];
        foreach ($stockValues as $value) {
            $amounts[] = $value->getValue();
            $dates[] = $value->getDate()->format('d/m/Y');
        }

        return $this->render(
            'part/infos_pieces.html.twig', [
            'totalStock' => $totalStock,
            'dates' => json_encode($dates),
            'amounts' => json_encode($amounts),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/appro/{id}', name: 'show_appro')]
    public function appro(Part $part): Response
    {
        $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNotesAppro($part->getId());
        return $this->render('part/showAppro.html.twig', ['deliveryNotes' => $deliveryNotes, 'part' => $part]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/partlabel/{id}', name: 'part_label')]
    public function printLabel(Part $part, PdfService $pdfService): Response
    {
        $html = $this->renderView('prints/one_label_print.html.twig', ['part' => $part]);
        $pdfService->printLabel($html);

        return new Response('', 200, ['Content-Type' => 'application/pdf']);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/stockTopValue', name: 'stock_top_value', methods: ["GET", "POST"])]
    public function topStockValue(): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $totalStock = $this->partRepository->findTotalStock($organisation);
        $stockValues = $this->stockValueRepository->findStockValues($organisation);
        $topParts = $this->partRepository->findTopValueParts($organisation);

        $amounts = [];
        $dates = [];
        foreach ($stockValues as $value) {
            $amounts[] = $value->getValue();
            $dates[] = $value->getDate()->format('d/m/Y');
        }

        return $this->render(
            'part/top_value_parts.html.twig', [
            'totalStock' => $totalStock,
            'dates' => json_encode($dates),
            'amounts' => json_encode($amounts),
            'parts' => $topParts,
            ]
        );
    }
}
