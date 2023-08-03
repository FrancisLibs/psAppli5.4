<?php

namespace App\Controller;

use App\Entity\Workshop;
use App\Form\WorkshopType;
use App\Repository\WorkshopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/workshop")
 */
class WorkshopController extends AbstractController
{
    private $workshopRepository;
    private $manager;

    public function __construct(EntityManagerInterface $manager, WorkshopRepository $workshopRepository)
    {
        $this->workshopRepository = $workshopRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/", name="workshop_index", methods={"GET"})
     */
    public function index(WorkshopRepository $workshopRepository): Response
    {
        return $this->render('workshop/index.html.twig', [
            'workshops' => $workshopRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="workshop_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $workshop = new Workshop();
        $form = $this->createForm(WorkshopType::class, $workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($workshop);
            $this->manager->flush();

            return $this->redirectToRoute('workshop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('workshop/new.html.twig', [
            'workshop' => $workshop,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="workshop_show", methods={"GET"})
     */
    public function show(Workshop $workshop): Response
    {
        return $this->render('workshop/show.html.twig', [
            'workshop' => $workshop,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="workshop_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Workshop $workshop): Response
    {
        $form = $this->createForm(WorkshopType::class, $workshop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute('workshop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('workshop/edit.html.twig', [
            'workshop' => $workshop,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="workshop_delete", methods={"POST"})
     */
    public function delete(Request $request, Workshop $workshop): Response
    {
        if ($this->isCsrfTokenValid('delete' . $workshop->getId(), $request->request->get('_token'))) {
            $this->manager->remove($workshop);
            $this->manager->flush();
        }

        return $this->redirectToRoute('workshop_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/upper", name="workshop_action", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function action(): Response
    {
        $workshops = $this->workshopRepository->findAll();
        foreach ($workshops as $workshop) {
            $workshop->setName(
                strtoupper($workshop->getName())
            );

            $this->manager->persist($workshop);
        }
        $this->manager->flush();

        return $this->redirectToRoute('workshop_index');
    }
}
