<?php

namespace App\Controller;

use App\Entity\Part;
use App\Form\PartType;
use App\Data\SearchPart;
use App\Form\SearchPartForm;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/part")
 */
class PartController extends AbstractController
{
    private $partRepository;
    private $manager;

    public function __construct(PartRepository $partRepository, EntityManagerInterface $manager)
    {
        $this->partRepository = $partRepository;
        $this->manager = $manager;
    }

    /**
     * @ Liste des pièces détachées
     * 
     * @Route("/", name="part_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     */
    public function index(Request $request): Response
    {
        $data = new SearchPart();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchPartForm::class, $data);
        $form->handleRequest($request);
        $parts = $this->partRepository->findSearch($data);
        //dd($parts);
        // if ($request->get('ajax')) {
        //     return new JsonResponse([
        //         'content'       =>  $this->renderView('ident/_idents.html.twig', ['idents' => $idents]),
        //         'sorting'       =>  $this->renderView('ident/_sorting.html.twig', ['idents' => $idents]),
        //         'pagination'    =>  $this->renderView('ident/_pagination.html.twig', ['idents' => $idents]),
        //     ]);
        // }
        return $this->render('part/index.html.twig', [
            'parts' =>  $parts,
            'form'  =>  $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="part_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $organisation = $this->getUser()->getOrganisation();
        $part = new Part();
        $form = $this->createForm(PartType::class, $part);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $part->setValidity(true);
            $part->setOrganisation($organisation);
            $part->setCode(strtoupper($part->getCode()));
            $part->setReference(strtoupper($part->getReference()));
            $part->setDesignation(ucfirst($part->getDesignation()));
            $part->getStock()->setPlace(strtoupper($part->getStock()->getPlace()));
            $entityManager->persist($part);
            $entityManager->flush();

            return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('part/new.html.twig', [
            'part' => $part,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="part_show", methods={"GET"})
     */
    public function show(Part $part): Response
    {
        return $this->render('part/show.html.twig', [
            'part' => $part,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="part_edit", methods={"GET","POST"})
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

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('part/edit.html.twig', [
            'part' => $part,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="part_delete", methods={"POST"})
     */
    public function delete(Request $request, Part $part): Response
    {
        if ($this->isCsrfTokenValid('delete' . $part->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($part);
            $entityManager->flush();
        }

        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }
}
