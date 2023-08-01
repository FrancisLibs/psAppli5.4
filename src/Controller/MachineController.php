<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Data\SearchMachine;
use App\Data\SearchIndicator;
use App\Form\SearchMachineForm;
use App\Form\SearchIndicatorType;
use App\Repository\MachineRepository;
use App\Repository\WorkorderRepository;
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
    private $workorderRepository;

    public function __construct(MachineRepository $machineRepository, EntityManagerInterface $manager, WorkorderRepository $workorderRepository, RequestStack $requestStack)
    {
        $this->machineRepository = $machineRepository;
        $this->workorderRepository = $workorderRepository;
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
     * @Route("/new/{parentId?}", name="machine_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request, ?int $parentId): Response
    {
        $user = $this->getUser();
        $organisation =  $user->getOrganisation();

        $machine = new Machine();

        if ($parentId) {
            $parent = $this->machineRepository->find($parentId);
            if ($parent->getChildLevel() == 0) {
                $machine->setWorkshop($parent->getWorkshop());
                $machine->setChildLevel(1);
                $machine->setParent($parent);
            } else {
                $this->get('session')->getFlashBag()->set('error', 'Une machine ne peut avoir qu\'un seul sous-niveau');
                return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
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

    private function readWorkorders($searchIndicator, $machineId)
    {
        $user = $this->getUser();
        $organisationId = $user->getOrganisation()->getId();

        if (empty($searchIndicator->startDate)) {
            $searchIndicator->startDate = new \DateTime('2022/01/01');
            $searchIndicator->endDate = new \DateTime('2023/12/31');
        };
        
        return $workorders = $this->workorderRepository->findAllWorkordersByMachine($organisationId, $searchIndicator, $machineId);
    }

    /**
     * @Route("/statistics/{id}", name="machine_statistics", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function machine_statistics(Machine $machine, Request $request): Response
    {
        $user = $this->getUser();
        $organisationId =  $user->getOrganisation()->getId();
        $machineId = $machine->getId();
        $searchIndicator = new SearchIndicator();

        $workorders = $this->readWorkorders($searchIndicator, $machineId);

        // Infos à extraire :
        // Nombre de BT/mois
        // Temps de Travail préventif/mois
        $preventiveTime = [];
        // Temps de travail curatif/mois
        $curativeTime = [];
        // Prix des pièces/mois
        $partsValue = [];

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->readWorkorders($searchIndicator, $machineId);
        }

        if ($workorders != null) {
            foreach ($workorders as $workorder) {
                if (!is_null($workorder->getStartDate())) {

                    // Mois du BT
                    $workorderDateMonth = $workorder->getStartDate()->format('m');
                    $workorderDateYear = (int)$workorder->getStartDate()->format('y');
                    $monthNumber = $workorderDateYear . "/" . $workorderDateMonth;

                    // Prix des pièces
                    $parts = $workorder->getPartsPrice();

                    if (array_key_exists($monthNumber, $partsValue)) {
                        $partsValue[$monthNumber] += $parts;
                    } else {
                        $partsValue[$monthNumber] = $parts;
                    }

                    // Temps de travail
                    $time = $this->manageTime($workorder);

                    if ($workorder->getpreventive()) {
                        if (array_key_exists($monthNumber, $preventiveTime)) {
                            $preventiveTime[$monthNumber] += $time;
                        } else {
                            $preventiveTime[$monthNumber] = $time;
                        }
                    } else {
                        if (array_key_exists($monthNumber, $curativeTime)) {
                            $curativeTime[$monthNumber] += $time;
                        } else {
                            $curativeTime[$monthNumber] = $time;
                        }
                    }
                }
            }

            // Sort of array and inverting months number
            $columns = array_keys($partsValue);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $partsValue);
            $partsValue = $this->invertingMonthNumber($partsValue);
            $partsValues = array_values($partsValue);

            $columns = array_keys($preventiveTime);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $preventiveTime);
            $preventiveTime = $this->invertingMonthNumber($preventiveTime);
            $preventiveTime = array_values($preventiveTime);

            $columns = array_keys($curativeTime);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $curativeTime);
            $curativeTime = $this->invertingMonthNumber($curativeTime);
            $curativeTime = array_values($curativeTime);

            $months = array_keys($partsValue);

            return $this->render('machine/stats.html.twig', [
                'form' => $form->createView(),
                'machine' => $machine,
                'months' =>  json_encode($months),
                'preventiveTime' =>  json_encode($preventiveTime),
                'curativeTime' => json_encode($curativeTime),
                'partsValue' => json_encode($partsValue),
            ]);
        }

        return $this->render('machine/stats.html.twig', [
            'form' => $form->createView(),
            'machine' => $machine,
            'months' =>  null,
            'preventiveTime' => null,
            'curativeTime' => null,
            'partsValue' => null,
        ]);
    }

    private function manageTime($workorder)
    {
        $minutes = $workorder->getDurationMinute();
        $hours = $workorder->getDurationHour();
        $days = $workorder->getDurationDay();
        $minutes = ($hours * 60) + ($days * 24 * 60) + $minutes;

        return $minutes;
    }

    private function invertingMonthNumber($array)
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
            $this->manager->flush();

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

        return $this->renderForm('machine/edit.html.twig', [
            'machine' => $machine,
            'form' => $form,
        ]);
    }
}
