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
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/machine")
 */
class MachineController extends AbstractController
{
    private $machineRepository;
    private $manager;
    private $requestStack;

    public function __construct(MachineRepository $machineRepository, EntityManagerInterface $manager, RequestStack $requestStack)
    {
        $this->machineRepository = $machineRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
    }

    /**
     * @ Liste des machines
     * 
     * @Route("/list/{mode?}/{documentId?}", name="machine_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param   Request $request
     * @param   string  $mode
     * @param   int     $documentId
     * 
     * @return  Response
     */
    public function index(Request $request, ?string $mode = null, ?int $documentId = null): Response
    {
        $machinesWithData = [];
        $session = $this->requestStack->getSession();

        // En mode "selectPreventive" ou "editpreventive"
        // on cherche les machines qu'on a mises dans la session
        if ($mode == "selectPreventive" || $mode == 'editPreventive') {
            $machines = $session->get('machines');
            // If machines in session
            if ($machines) {
                foreach ($machines as $id) {
                    $machinesWithData[] = $this->machineRepository->find($id);
                }
            }
        }
        // Reprise de l'ancienne recherche lors de la selection des machines pour un préventif
        $dataMachinePreventive = $session->get('dataMachinePreventive');
        $data = new SearchMachine();

        // if ($mode == "selectPreventive" && $dataMachinePreventive) {
        //     $data = $dataMachinePreventive;
        // }

        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchMachineForm::class, $data);
        $form->handleRequest($request);
        $machines = $this->machineRepository->findSearch($data);

        // if ($mode == "selectPreventive") { // Sauvegarde de la classe de tri des machines
        //     $session->set('dataMachinePreventive', $data);
        // }

        if ($request->get('ajax') && ($mode == 'newWorkorder' || $mode == null)) {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' => $machines, 'mode' => $mode]),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        if ($request->get('ajax') && $mode == 'modif') {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' => $machines, 'mode' => $mode, 'documentId' => $documentId]),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        if ($request->get('ajax') && ($mode == 'selectPreventive' || $mode = 'editPreventive')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' => $machines, 'mode' => $mode, 'documentId' => $documentId]),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('machine/_machines.html.twig', ['machines' => $machines, 'mode' => null]),
                'sorting'       =>  $this->renderView('machine/_sorting.html.twig', ['machines' => $machines]),
                'pagination'    =>  $this->renderView('machine/_pagination.html.twig', ['machines' => $machines]),
            ]);
        }

        return $this->render('machine/index.html.twig', [
            'machines'          =>  $machines,
            'form'              =>  $form->createView(),
            'mode'              =>  $mode,
            'documentId'        =>  $documentId,
            'machinesWithData'  =>  $machinesWithData,

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
            $machine->setCreatedAt((new \Datetime()));
            $machine->setStatus(true);
            $machine->setInternalCode(strtoupper($machine->getInternalCode()));
            $machine->setConstructor(strtoupper($machine->getConstructor()));
            $machine->setDesignation(mb_strtoupper($machine->getDesignation()));
            $machine->setActive(true);
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
     * @Route("/show/Workorder/{id}", name="machine_workorders", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function machine_workorder(Machine $machine): Response
    {

        return $this->render('machine/workorders.html.twig', [
            'machine' => $machine,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="machine_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Machine $machine): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setInternalCode(strtoupper($machine->getInternalCode()));
            $machine->setConstructor(strtoupper($machine->getConstructor()));
            $machine->setDesignation(mb_strtoupper($machine->getDesignation()));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('machine_show', [
                'id' => $machine->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="machine_delete", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function delete(Request $request, Machine $machine): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $token)) {
            $machine->setActive(false);
            $this->manager->flush();
        }

        return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/copy/{id}", name="machine_copy", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function COPY(Request $request, Machine $machine): Response
    {
        $newMachine = new Machine();

        $newMachine = clone $machine;

        $form = $this->createForm(MachineType::class, $newMachine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newMachine->setCreatedAt(new \Datetime());

            $manager = $this->getDoctrine()->getManager();

            $manager->persist($newMachine);
            $manager->flush();

            return $this->redirectToRoute(
                'machine_show',
                [
                    'id' => $newMachine->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/action", name="machine_action", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function action(): Response
    {
        $machines = $this->machineRepository->findAll();
        foreach ($machines as $machine) {
            $machine->setConstructor(
                strtoupper($machine->getConstructor())
            );
            $machine->setDesignation(
                mb_strtoupper($machine->getDesignation())
            );

            $this->manager->persist($machine);
        }
        $this->manager->flush();

        return $this->redirectToRoute('machine_index');
    }
}
