<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Entity\Workorder;
use App\Form\ScheduleType;
use App\Form\WorkorderType;
use App\Form\PreventiveType;
use App\Data\SearchPreventive;
use App\Form\SearchPreventiveForm;
use App\Repository\MachineRepository;
use App\Repository\ScheduleRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

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

    public function __construct(
        EntityManagerInterface $manager,
        WorkorderRepository $workorderRepository,
        ScheduleRepository $scheduleRepository,
        MachineRepository $machineRepository,
        RequestStack $requestStack
    ) {
        $this->manager = $manager;
        $this->workorderRepository = $workorderRepository;
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
     * @return Response|RedirectResponse
     */
    public function create(Request $request, Machine $machine = null): Response
    {
        // Récupération des machines lors d'un BT préventif
        $session = $this->requestStack->getSession();
        $machinesWithData = [];
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
        $workorder->setStatus(Workorder::EN_COURS);
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
            if(!empty($machines)){
                // Ajout de machine au BT préventif
                foreach ($machines as $id) {
                    $workorder->addMachine($this->machineRepository->find($id));
                }

                // suppression des machines en session
                $session->remove('machines');

                $this->manager->persist($workorder);
                $this->manager->flush();

                return $this->redirectToRoute('work_order_show', [
                        'id' => $workorder->getId()
                    ], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('error', 'Il n\'y a pas de machine dans le BT');
        }

        return $this->renderForm('preventive/new.html.twig', [
            'workorder' => $workorder,
            'form' => $form,
            'machinesWithData' => $machinesWithData,
        ]);
    }
}
