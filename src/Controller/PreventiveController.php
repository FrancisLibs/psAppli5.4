<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Entity\Workorder;
use App\Form\ScheduleType;
use App\Form\WorkorderType;
use App\Form\PreventiveType;
use App\Data\SearchPreventive;
use App\Form\WorkorderEditType;
use App\Form\SearchPreventiveForm;
use App\Repository\MachineRepository;
use App\Repository\ScheduleRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/preventive")
 */
class PreventiveController extends AbstractController
{
    private $manager;
    private $workorderRepository;
    private $scheduleRepository;
    private $requestStack;
    private $machineRepository;
    private $workorderStatusRepository;

    public function __construct(
        WorkorderStatusRepository $workorderStatusRepository,
        WorkorderRepository $workorderRepository,
        ScheduleRepository $scheduleRepository,
        MachineRepository $machineRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack
    ) {
        $this->manager = $manager;
        $this->workorderRepository = $workorderRepository;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->requestStack = $requestStack;
        $this->machineRepository = $machineRepository;
    }

    /**
     * Liste des BT préventifs
     * 
     * @Route("/", name="preventive_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request 
     * @return Response 
     */
    public function index(Request $request): Response
    {
        // Vidange de la session s'il reste ds machines inutilisées
        $this->emptyMachineCart($request);

        $data = new SearchPreventive();

        $data->page = $request->get('page', 1);
        $data->organisation = $this->getUser()->getOrganisation();
        $data->preventive = true;

        $form = $this->createForm(SearchPreventiveForm::class, $data);

        $form->handleRequest($request);

        $workorders = $this->workorderRepository->findPreventiveWorkorders($data);
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('workorder/_workorders.html.twig', ['workorders' => $workorders]),
                'sorting'       =>  $this->renderView('workorder/_sorting.html.twig', ['workorders' => $workorders]),
                'pagination'    =>  $this->renderView('workorder/_pagination.html.twig', ['workorders' => $workorders]),
            ]);
        }

        return $this->render('preventive/index.html.twig', [
            'workorders' =>  $workorders,
            'form'  =>  $form->createView(),
        ]);
    }

    /**
     * Création d'un BT préventif
     * 
     * @Route("/new/{id?}", name="preventive_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Machine $machine
     * @param Request 
     * @return Response
     */
    public function create(Request $request, Machine $machine = null): Response
    {
        // Récupération des machines lors d'un BT préventif
        $machinesWithData = [];
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        if ($machines) {
            foreach ($machines as $id) {
                $machinesWithData[] = $this->machineRepository->find($id);
            }
        }
        $user = $this->getUser();
        $workorder = new Workorder();
        $workorder->setCreatedAt(new \DateTime());
        $workorder->setOrganisation($user->getOrganisation());
        $workorder->setUser($user);
        $workorder->setType(Workorder::PREVENTIF);
        $status = $this->workorderStatusRepository->findOneBy(['name' => 'EN_COURS']);
        $workorder->setWorkorderStatus($status);
        $workorder->setPreventive(true);
        $workorder->setTemplate(true);

        $form = $this->createForm(WorkorderType::class, $workorder);
        $form->remove('implementation')
            ->remove('type')
            ->remove('startDate')
            ->remove('startTime')
            ->remove('endDate')
            ->remove('endTime')
            ->remove('durationDay')
            ->remove('durationHour')
            ->remove('durationMinute')
            ->remove('stopTimeHour')
            ->remove('stopTimeMinute')
            ->add('schedule', ScheduleType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Contrôle si machine en session
            $machines = $session->get('machines', []);
            if (!empty($machines)) {
                // Ajout des machines au BT préventif
                foreach ($machines as $id) {
                    $workorder->addMachine($this->machineRepository->find($id));
                }

                // suppression des machines en session
                $session->remove('machines');

                $this->manager->persist($workorder);
                $this->manager->flush();

                return $this->render('preventive/show.html.twig', [
                    'workorder' => $workorder
                ]);
            }
            $this->addFlash('error', 'Il n\'y a pas de machine dans le BT');
        }
        return $this->renderForm('preventive/new.html.twig', [
            'workorder' => $workorder,
            'form' => $form,
            'mode' => 'newPreventive',
            'machinesWithData' => $machinesWithData,
        ]);
    }

    /**
     * @Route("/{id}", name="preventive_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Workorder $workorder): Response
    {
        return $this->render('preventive/show.html.twig', [
            'workorder' => $workorder,
        ]);
    }

    /**
     * @Route("/edit/{id}/{mode?}", name="preventive_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Workorder $workorder, ?string $mode): Response
    {
        if ($mode == 'editPreventive') {
            // Attribution des éventuelles machines en session au BT préventif
            $session = $this->requestStack->getSession();
            $machines = $session->get('machines');
            if ($machines) {
                foreach ($machines as $key => $id) {
                    $machine = $this->machineRepository->find($id);
                    $workorder->addMachine($machine);
                    unset($machines[$key]);
                }
                $session->set('machines', $machines);
                $this->manager->flush();
            }
        }
        $form = $this->createForm(WorkorderEditType::class, $workorder);
        $form->remove('implementation')
            ->remove('type')
            ->remove('startDate')
            ->remove('startTime')
            ->remove('endDate')
            ->remove('endTime')
            ->remove('durationDay')
            ->remove('durationHour')
            ->remove('durationMinute')
            ->remove('stopTimeHour')
            ->remove('stopTimeMinute')
            ->add('schedule', ScheduleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérification si machines dans le BT
            $machines = $workorder->getMachines();
            if (!$machines->isEmpty()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute(
                    'preventive_show',
                    [
                        'id' => $workorder->getId()
                    ],
                    Response::HTTP_SEE_OTHER
                );
            }
            $this->addFlash('error', 'Il n\'y a pas de machine dans le BT');
        }
        return $this->renderForm('preventive/edit.html.twig', [
            'workorder' => $workorder,
            'form' => $form,
            'mode' => 'editPreventive',
        ]);
    }

    /**
     * Enlever une machine du BT
     * 
     * @Route("/machine/remove/{id}/{machine}", name="preventive_machine_remove", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request 
     * @return Response 
     */
    public function removeMachine(Workorder $workorder, Machine $machine)
    {
        $machine = $this->machineRepository->find($machine);
        $workorder->removeMachine($machine);
        $this->manager->flush();

        return $this->redirectToRoute(
            'preventive_edit',
            [
                'id' => $workorder->getId()
            ],
            Response::HTTP_SEE_OTHER
        );
    }

    public function emptyMachineCart(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        foreach ($machines as $cle => $value) {
            unset($machines[$cle]);
        }
        $session->set('machines', $machines);
        return $this->redirectToRoute('preventive_index');
    }
}
