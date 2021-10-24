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
     * @Route("/", name="part_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     */
    public function index(Request $request): Response
    {
        // Utilisation de la session pour sauvegarder l'objet de recherche
        $session = $this->requestStack->getSession();

        $user = $this->getUser();
        $organisation = $user->getOrganisation();

        $data = new SearchPart();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchPartForm::class, $data, [
            'organisation' => $organisation
        ]);

        $form->handleRequest($request);
        $session->set('data', $data); // Sauvegarde de la recherche

        $parts = $this->partRepository->findSearch($data);
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('part/_parts.html.twig', ['parts' => $parts]),
                'sorting'       =>  $this->renderView('part/_sorting.html.twig', ['parts' => $parts]),
                'pagination'    =>  $this->renderView('part/_pagination.html.twig', ['parts' => $parts]),
            ]);
        }
        return $this->render('part/index.html.twig', [
            'parts' =>  $parts,
            'form'  =>  $form->createView(),
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
            $part->setValidity(true);
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

            return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
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
            $this->manager->remove($part);
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
        }
        $this->manager->flush();

        return $this->redirectToRoute('part_index', [], Response::HTTP_SEE_OTHER);
    }
}
