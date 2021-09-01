<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Data\SearchMachine;
use App\Form\SearchMachineForm;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/machine")
 */
class MachineController extends AbstractController
{
    private $machineRepository;
    private $manager;

    public function __construct(MachineRepository $machineRepository, EntityManagerInterface $manager)
    {
        $this->machineRepository = $machineRepository;
        $this->manager = $manager;
    }

    /**
     * @ Liste des machines
     * 
     * @Route("/", name="machine_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request $request
     */
    public function index(Request $request): Response
    {
        $data = new SearchMachine();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchMachineForm::class, $data);
        $form->handleRequest($request);
        $machines = $this->machineRepository->findSearch($data);


        return $this->render('machine/index.html.twig', [
            'machines'  => $machines,
            'form'      =>  $form->createView(),
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
