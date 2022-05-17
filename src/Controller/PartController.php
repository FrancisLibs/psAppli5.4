<?php

namespace App\Controller;

use App\Entity\Part;
use App\Form\PartType;
use App\Data\SearchPart;
use App\Service\PdfService;
use App\Form\SearchPartForm;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/part")
 */
class PartController extends AbstractController
{
    private $partRepository;
    private $manager;
    private $requestStack;

    public function __construct(PartRepository $partRepository, EntityManagerInterface $manager, RequestStack $requestStack)
    {
        $this->partRepository = $partRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
    }

    /**
     * @ Liste des pièces détachées
     * 
     * @Route("/list/{mode?}/{documentId?}", name="part_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request $request
     * @param int documentId
     * @param string mode
     */
    public function index(Request $request, ?string $mode = null, ?int $documentId = null): Response
    {
        $user = $this->getUser();
        $organisation =  $user->getOrganisation();
        $session = $this->requestStack->getSession();

        $data = new SearchPart();

        if ($mode == "workorderAddPart" || $mode == "newDeliveryNote" || $mode == "editDeliveryNote") {
            $data = $session->get('data', null);
            if (!$data) {
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
            return new JsonResponse([
                'content'       =>  $this->renderView('part/_parts.html.twig', ['parts' => $parts, 'mode' => $mode, 'documentId' => $documentId]),
                'sorting'       =>  $this->renderView('part/_sorting.html.twig', ['parts' => $parts]),
                'pagination'    =>  $this->renderView('part/_pagination.html.twig', ['parts' => $parts]),
            ]);
        }

        if ($mode == 'workorderAddPart' || $mode == 'editReceivedPart' || $mode == 'editDeliveryNote' || $mode == 'newDeliveryNote') {
            $panier = $session->get('panier', []);
            if ($panier) {
                $panierWithData = [];
                foreach ($panier as $id => $quantity) {
                    $panierWithData[] = [
                        'part' => $this->partRepository->find($id),
                        'quantity' => $quantity,
                    ];
                }
                return $this->render('part/index.html.twig', [
                    'parts' =>  $parts,
                    'form'  =>  $form->createView(),
                    'mode'  =>  $mode,
                    'items' => $panierWithData,
                    'documentId' => $documentId,
                ]);
            }
            return $this->render('part/index.html.twig', [
                'parts' =>  $parts,
                'form'  =>  $form->createView(),
                'documentId' => $documentId,
                'mode'  =>  $mode,
            ]);
        }
        return $this->render('part/index.html.twig', [
            'parts' =>  $parts,
            'form'  =>  $form->createView(),
            'documentId' => $documentId,
            'mode'  =>  "index",
        ]);
    }

    /**
     * @Route("/new", name="part_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request): Response
    {
        $organisation = $this->getUser()->getOrganisation();
        $part = new Part();
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

            return $this->redirectToRoute('part_show', [
                'id' => $part->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('part/new.html.twig', [
            'part' => $part,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/show/{id}", name="part_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Part $part): Response
    {
        return $this->render('part/show.html.twig', [
            'part' => $part,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="part_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
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

            return $this->redirectToRoute('part_show', [
                'id' => $part->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('part/edit.html.twig', [
            'part' => $part,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="part_delete", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function delete(Request $request, Part $part): Response
    {
        if ($this->isCsrfTokenValid('delete' . $part->getId(), $request->request->get('_token'))) {
            $part->setActive(false);
            $this->manager->flush();
        }

        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/approzero", name="appro_to_zero")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function setApproToZero(): Response
    {
        $parts = $this->partRepository->findAll();

        foreach ($parts as $part) {
            $approQte = $part->getStock()->getApproQte();
            if ($approQte == null) {
                $part->getStock()->setApproQte(0);
                $this->manager->persist($part);
            }
            if ($part->getActive() == false) {
                $part->setActive(true);
            }
        }
        $this->manager->flush();

        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/reception", name="reception_part")
     * @Security("is_granted('ROLE_USER')")
     */
    public function reception(): Response
    {


        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }

    // /**
    //  * @Route("/action", name="part_action")
    //  * @Security("is_granted('ROLE_ADMIN')")
    //  */
    // public function action(): Response
    // {
    //     $parts = $this->partRepository->findAll();

    //     foreach ($parts as $part) {
    //         $part->setDesignation(strtoupper($part->getDesignation()));
    //         $part->setReference(strtoupper($part->getReference()));
    //         $this->manager->persist($part);
    //     }

    //     $this->manager->flush();

    //     return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    // }

    /**
     * @Route("/infos", name="parts_infos", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function infos(): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $totalStock = $this->partRepository->findTotalStock($organisation);

        return $this->render('part/infos_pieces.html.twig', [
            'totalStock' => $totalStock,
        ]);
    }

    /**
     * Impession étiquette d'une pièce 
     * 
     * @Route("/partlabel/{id}", name="part_label")
     * @Security("is_granted('ROLE_USER')")
     */
    public function printLabel(Part $part, PdfService $pdfService): Response
    {
        $html = $this->renderView('prints/one_label_print.html.twig', [
            'part' => $part,
        ]);

        $pdfService->printLabel($html);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
