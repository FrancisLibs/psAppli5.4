<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/machine")
 */
class MachineController extends AbstractController
{
    /**
     * @Route("/", name="machine_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator, MachineRepository $machineRepository ): Response
    {
        $machines = $machineRepository->findAll();

        $machines = $paginator->paginate(
            $machines, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('machine/list.html.twig', [
            'machines' => $machines,
        ]);
    }

    /**
     * @Route("/new", name="machine_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($machine);
            $entityManager->flush();

            return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('machine/new.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="machine_show", methods={"GET"})
     */
    public function show(Machine $machine): Response
    {
        return $this->render('machine/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="machine_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Machine $machine): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="machine_delete", methods={"POST"})
     */
    public function delete(Request $request, Machine $machine): Response
    {
        if ($this->isCsrfTokenValid('delete'.$machine->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($machine);
            $entityManager->flush();
        }

        return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
    }
}
