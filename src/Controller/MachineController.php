<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Data\SearchMachine;
use App\Form\SearchMachineForm;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/{mode?}/{workorderId?}", name="machine_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request   $request
     * @param Boolean   $select
     * @return Response
     */
    public function index(Request $request, $mode = null, ?int $workorderId): Response
    {
        // dump($mode);
        // dd($workorderId);
        $data = new SearchMachine();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchMachineForm::class, $data);
        $form->handleRequest($request);
        $machines = $this->machineRepository->findSearch($data);
        if ($request->get('ajax') && $mode == 'select') {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' => $machines, 'mode' => 'select']),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        if ($request->get('ajax') && $mode == 'modif') {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' => $machines, 'mode' => 'modif', 'workorderId' => $workorderId]),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' =>$machines, 'mode' => null]),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        return $this->render('machine/index.html.twig', [
            'machines'      =>  $machines,
            'form'          =>  $form->createView(),
            'mode'          =>  $mode,
            'workorderId'   => $workorderId,
        ]);
    }

    /**
     * @Route("/new", name="machine_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request): Response
    {
        $machine = new Machine();
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setStatus(true);
            $this->manager->persist($machine);
            $this->manager->flush();

            return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('machine/new.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/show/{id}", name="machine_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Machine $machine): Response
    {
        return $this->render('machine/show.html.twig', [
            'machine' => $machine,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="machine_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
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
     * @Security("is_granted('ROLE_USER')")
     */
    public function delete(Request $request, Machine $machine): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$machine->getId(), $token)) {
            $machine->setStatus(false);
            $this->manager->flush();
        }

        return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
    }
}
