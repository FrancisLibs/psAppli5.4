<?php

namespace App\Controller;

use App\Entity\Part;
use App\Form\PartType;
use App\Data\SearchPart;
use App\Form\SearchPartForm;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

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
     */
    public function index(Request $request, ?int $documentId = null, ?string $mode = null): Response
    {
        // Utilisation de la session pour sauvegarder l'objet de recherche
        $session = $this->requestStack->getSession();

        $data = new SearchPart();

        if ($mode == "workorderAddPart") {
            $data = $session->get('data');
        } 
        
        $organisation = $this->getUser()->getOrganisation();
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

        if ($mode == 'workorderAddPart' || $mode == 'receivedPart') {
            $panier = $session->get('panier', []);
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
                'documentId' => $documentId,
                'items' => $panierWithData,
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

            return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
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
            $part->setDesignation(ucfirst($part->getDesignation()));
            $part->getStock()->setPlace(strtoupper($part->getStock()->getPlace()));

            $this->manager->flush();

            return $this->redirectToRoute('part_index', [
                'edit' => true,
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
}
