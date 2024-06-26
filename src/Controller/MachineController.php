<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Data\SearchMachine;
use App\Data\SearchIndicator;
use App\Form\SearchMachineForm;
use App\Form\SearchIndicatorType;
use App\Service\OrganisationService;
use App\Repository\MachineRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/machine")
 */

class MachineController extends AbstractController
{
    protected $machineRepository;
    protected $manager;
    protected $requestStack;
    protected $workorderRepository;
    protected $organisation;


    public function __construct(
        OrganisationService $organisation,
        MachineRepository $machineRepository,
        EntityManagerInterface $manager,
        WorkorderRepository $workorderRepository,
        RequestStack $requestStack
    ) {
        $this->machineRepository = $machineRepository;
        $this->workorderRepository = $workorderRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->organisation = $organisation;
    }


    /**
     * @ Liste des machines
     *
     * @param Request $request
     * @param string  $mode
     * @param int     $documentId
     *
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/list/{mode?}/{documentId?}', name: 'machine_index', methods: ["GET"])]
    public function index(
        Request $request,
        ?string $mode = null,
        ?int $documentId = null
    ): Response {
        $machinesWithData = [];
        $session = $this->requestStack->getSession();

        // En mode "selectPreventive" ou "editpreventive".
        // On cherche les machines qu'on a mises dans la session.
        if ($mode === "selectPreventive" || $mode === 'editPreventive') {
            $machines = $session->get('machines');
            // If machines in session.
            if ($machines) {
                foreach ($machines as $id) {
                    $machinesWithData[] = $this->machineRepository->find($id);
                }
            }
        }
        
        // Reprise de l'ancienne recherche lors.
        // De la selection des machines pour un préventif.
        // $dataMachinePreventive = $session->get('dataMachinePreventive');
        $data = new SearchMachine();

        // If ($mode == "selectPreventive" && $dataMachinePreventive) {.
        //     $data = $dataMachinePreventive.
        // }.

        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchMachineForm::class, $data);
        $form->handleRequest($request);
        $machines = $this->machineRepository->findSearch($data);

        if ($request->get('ajax') && ($mode === 'newWorkorder' || $mode === null)) {
            return new JsonResponse(
                [
                    'content' =>  $this->renderView(
                        'machine/_machines.html.twig',
                        ['machines' => $machines, 'mode' => $mode]
                    ),
                    'sorting' =>  $this->renderView(
                        'machine/_sorting.html.twig',
                        ['machines' => $machines]
                    ),
                    'pagination' =>  $this->renderView(
                        'machine/_pagination.html.twig',
                        ['machines' => $machines]
                    ),
                ]
            );
        }

        if ($request->get('ajax') && $mode === 'modif') {
            return new JsonResponse(
                [
                    'content' =>  $this->renderView(
                        'machine/_machines.html.twig',
                        [
                            'machines' => $machines,
                            'mode' => $mode,
                            'documentId' => $documentId
                        ]
                    ),
                    'sorting' =>  $this->renderView(
                        'machine/_sorting.html.twig',
                        ['machines' => $machines]
                    ),
                    'pagination' =>  $this->renderView(
                        'machine/_pagination.html.twig',
                        ['machines' => $machines]
                    ),
                ]
            );
        }

        if ($request->get('ajax')
            && ($mode === 'selectPreventive' || $mode === 'editPreventive')
        ) {
            return new JsonResponse(
                [
                    'content' => $this->renderView(
                        'machine/_machines.html.twig',
                        [
                            'machines' => $machines,
                            'mode' => $mode,
                            'documentId' => $documentId
                        ]
                    ),
                    'sorting' => $this->renderView(
                        'machine/_sorting.html.twig',
                        ['machines' => $machines]
                    ),
                    'pagination' => $this->renderView(
                        'machine/_pagination.html.twig',
                        ['machines' => $machines]
                    ),
                ]
            );
        }

        if ($request->get('ajax')) {
            return new JsonResponse(
                [
                    'content' => $this->renderView(
                        'machine/_machines.html.twig',
                        [
                            'machines' => $machines,
                            'mode' => null
                        ]
                    ),
                    'sorting' => $this->renderView(
                        'machine/_sorting.html.twig',
                        ['machines' => $machines]
                    ),
                    'pagination' => $this->renderView(
                        'machine/_pagination.html.twig',
                        ['machines' => $machines]
                    ),
                ]
            );
        }

        return $this->render(
            'machine/index.html.twig',
            [
                'machines' => $machines,
                'form' => $form->createView(),
                'mode' => $mode,
                'documentId' => $documentId,
                'machinesWithData' => $machinesWithData,

            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/new/{parentId?}',
        name: 'machine_new',
        methods: ["GET", "POST"]
    )]
    public function new(Request $request, ?int $parentId): Response
    {
        $organisation = $this->organisation->getOrganisation();

        $machine = new Machine();

        if ($parentId) {
            $parent = $this->machineRepository->find($parentId);
            if ($parent->getChildLevel() === 0) {
                $machine->setWorkshop($parent->getWorkshop());
                $machine->setChildLevel(1);
                $machine->setParent($parent);
            } else {
                $this->addFlash(
                    'error',
                    'Une machine ne peut avoir qu\'un seul sous-niveau'
                );
                return $this->redirectToRoute(
                    'machine_index',
                    [],
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setCreatedAt((new \Datetime()));
            $machine->setStatus(true);
            $machine->setInternalCode(strtoupper($machine->getInternalCode()));
            $machine->setConstructor(strtoupper($machine->getConstructor()));
            $machine->setDesignation(mb_strtoupper($machine->getDesignation()));
            $machine->setActive(true);
            $machine->setOrganisation($organisation);
            $this->manager->persist($machine);
            $this->manager->flush();

            return $this->redirectToRoute(
                'machine_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'machine/new.html.twig',
            [
                'machine' => $machine,
                'form' => $form,
            ]
        );
    }

    /**
     * @Route("/show/{id}",                 name="machine_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Machine $machine): Response
    {
        return $this->render(
            'machine/show.html.twig',
            [
                'machine' => $machine,
            ]
        );
    }

    private function _readWorkorders($searchIndicator, $machineId)
    {
        $organisationId = $this->organisation->getOrganisation()->getId();

        if (empty($searchIndicator->startDate)) {
            $sDate = new \DateTime();
            $sDate->setDate($sDate->format('Y'), 1, 1);

            $eDate = new \DateTime();
            $eDate->setDate($eDate->format('Y'), 12, 31);

            $searchIndicator->startDate = $sDate;
            $searchIndicator->endDate = $eDate;
        };

        $workorders = $this->workorderRepository->findAllWorkordersByMachine(
            $organisationId,
            $searchIndicator,
            $machineId
        );
        return $workorders;
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/statistics/{id}',
        name: 'machine_statistics',
        methods: ["GET", "POST"]
    )]
    public function machineStatistics(Machine $machine, Request $request): Response
    {
        $machineId = $machine->getId();
        $searchIndicator = new SearchIndicator();

        // Lecture des BT.
        $workorders = $this->_readWorkorders($searchIndicator, $machineId);

        // Temps de Travail préventif/mois.
        $preventiveTime = [];
        // Temps de travail curatif/mois.
        $curativeTime = [];
        // Prix des pièces/mois.
        $partsValue = [];

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator, $machineId);
        }

        // S'il y a des BT.
        if ($workorders != null) {
            foreach ($workorders as $workorder) {
                if (!is_null($workorder->getStartDate())) {

                    // Mois du BT.
                    $workorderDateMonth = $workorder->getStartDate()->format('m');
                    $workorderDateYear = (int)$workorder
                        ->getStartDate()
                        ->format('y');
                    $date = $workorderDateYear . "/" . $workorderDateMonth;

                    // Prix des pièces.
                    $parts = $workorder->getPartsPrice();

                    if (array_key_exists($date, $partsValue)) {
                        $partsValue[$date] += $parts;
                    } else {
                        $partsValue[$date] = $parts;
                    }

                    // Temps de travail.
                    $time = $this->_manageTime($workorder);

                    if ($workorder->getpreventive()) {
                        if (array_key_exists($date, $preventiveTime)) {
                            $preventiveTime[$date] += $time;
                        } else {
                            $preventiveTime[$date] = $time;
                        }
                    } else {
                        if (array_key_exists($date, $curativeTime)) {
                            $curativeTime[$date] += $time;
                        } else {
                            $curativeTime[$date] = $time;
                        }
                    }
                }
            }

            // Comptage du nombre de BT.
            $preventiveBt = [];
            $curativeBt = [];
        
            foreach ($workorders as $workorder) {
                if ($workorder->getPreventive()) {
                    array_push($preventiveBt, $workorder);
                } else {
                    array_push($curativeBt, $workorder);
                }
            }

            // Nombre de BT/type.
            $totalWorkorders = count($workorders);
            $totalPreventive = count($preventiveBt);
            $totalCurative = count($curativeBt);


            // Sort of array and format months number.
            $columns = array_keys($partsValue);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $partsValue);
            $partsValue = $this->_invertingMonthNumber($partsValue);
            $partsValues = array_values($partsValue);

            $columns = array_keys($preventiveTime);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $preventiveTime);
            $preventiveTime = $this->_invertingMonthNumber($preventiveTime);
            $preventiveTime = array_values($preventiveTime);

            $columns = array_keys($curativeTime);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $curativeTime);
            $curativeTime = $this->_invertingMonthNumber($curativeTime);
            $curativeTime = array_values($curativeTime);

            $months = array_keys($partsValue);

            return $this->render(
                'machine/stats.html.twig',
                [
                    'form' => $form->createView(),
                    'machine' => $machine,
                    'months' => json_encode($months),
                    'preventiveTime' => json_encode($preventiveTime),
                    'curativeTime' => json_encode($curativeTime),
                    'partsValue' => json_encode($partsValue),
                    'totalWorkorder' => $totalWorkorders,
                    'totalPreventive' => $totalPreventive,
                    'totalCurative' => $totalCurative,
                ]
            );
        }

        // Nombre de BT/type.
        $totalWorkorders = 0;
        $totalPreventive = 0;
        $totalCurative = 0;

        return $this->render(
            'machine/stats.html.twig',
            [
                'form' => $form->createView(),
                'machine' => $machine,
                'months' => null,
                'preventiveTime' => null,
                'curativeTime' => null,
                'partsValue' => null,
                'totalWorkorder' => null,
                'totalPreventive' => null,
                'totalCurative' => null,
            ]
        );
    }

    private function _manageTime($workorder)
    {
        $minutes = $workorder->getDurationMinute();
        $hours = $workorder->getDurationHour();
        $days = $workorder->getDurationDay();
        $minutes = ($hours * 60) + ($days * 24 * 60) + $minutes;

        return $minutes;
    }

    private function _invertingMonthNumber($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            // $month = substr($key, 3, 2);
            // $year = substr($key, 0, 2);
            $key = substr($key, 3, 2) . "/" . substr($key, 0, 2);
            $result[$key] = $value;
        }
        return $result;
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/edit/{id}',
        name: 'machine_edit',
        methods: ["GET", "POST"]
    )]
    public function edit(Request $request, Machine $machine): Response
    {
        $form = $this->createForm(MachineType::class, $machine);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machine->setInternalCode(strtoupper($machine->getInternalCode()));
            $machine->setConstructor(strtoupper($machine->getConstructor()));
            $machine->setDesignation(mb_strtoupper($machine->getDesignation()));
            $this->manager->flush();

            return $this->redirectToRoute(
                'machine_show',
                [
                    'id' => $machine->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'machine/edit.html.twig',
            [
                'machine' => $machine,
                'form' => $form,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/delete/{id}',
        name: 'machine_delete',
        methods: ["POST"]
    )]
    public function delete(Request $request, Machine $machine): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $machine->getId(), $token)) {
            $machine->setActive(false);
            $this->manager->flush();
        }

        return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/copy/{id}',
        name: 'machine_copy',
        methods: ["GET", "POST"]
    )]
    public function COPY(Request $request, Machine $machine): Response
    {
        $newMachine = new Machine();

        $newMachine = clone $machine;

        $form = $this->createForm(MachineType::class, $newMachine);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newMachine->setCreatedAt(new \Datetime());


            $this->manager->persist($newMachine);
            $this->manager->flush();

            return $this->redirectToRoute(
                'machine_show',
                [
                    'id' => $newMachine->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'machine/edit.html.twig',
            [
                'machine' => $machine,
                'form' => $form,
            ]
        );
    }
}

