<?php

namespace App\Controller;

use App\Data\SearchIndicator;
use App\Form\SearchIndicatorType;
use App\Service\OrganisationService;
use App\Repository\WorkorderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndicatorController extends AbstractController
{
    private $_workorderRepository;
    private $_organisation;


    public function __construct(
        WorkorderRepository $workorderRepository, 
        OrganisationService $organisation
    ) {
        $this->_workorderRepository = $workorderRepository;
        $this->_organisation=$organisation;
    }


    private function _readWorkorders($searchIndicator)
    {
        $organisation =  $this->_organisation->getOrganisation();

        if (empty($searchIndicator->startDate)) {
            $currentYear = new \DateTime();
            $currentYear= $currentYear->format('Y');
            $firstDay = new \DateTime();
            $firstDay->setDate($currentYear, 01, 01);
            $firstDay->setTime(0, 0, 0);
            $endDay = new \DateTime();
            $endDay->setDate($currentYear, 12, 31);
            $endDay->setTime(23, 59, 59);
            $searchIndicator->startDate = $firstDay;
            $searchIndicator->endDate = $endDay;
        };

        $workorders = $this->_workorderRepository->findIndicatorsWorkorders(
            $organisation, 
            $searchIndicator
        );

        return $workorders;
    }

    #[Route('/indicator/workTime', name: 'app_work_time')]
    #[IsGranted('ROLE_USER')]
    public function workTime(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();

        $workorders = $this->_readWorkorders($searchIndicator);
        $datas = $this->_workTimeProcess($workorders) ?? [];

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator);
            if ($workorders) {
                $datas = $this->_workTimeProcess($workorders);

                return $this->render(
                    'indicator/workorderTimes.html.twig', [
                    'form' => $form->createView(),
                    'machineDatas' => $datas['machineDatas'],
                    'totalDays' => $datas['totalDays'],
                    'totalHours' => $datas['totalHours'],
                    'totalMinutes' => $datas['totalMinutes']
                    ]
                );
            }

            return $this->render(
                'indicator/workorderTimes.html.twig', [
                'form' => $form->createView(),
                'totalDays' => $datas['totalDays'],
                'totalHours' => $datas['totalHours'],
                'totalMinutes' => $datas['totalMinutes']
                ]
            );
        }

        return $this->render(
            'indicator/workorderTimes.html.twig', [
            'form' => $form->createView(),
            'machineDatas' => $datas['machineDatas'],
            'totalDays' => $datas['totalDays'],
            'totalHours' => $datas['totalHours'],
            'totalMinutes' => $datas['totalMinutes']
            ]
        );
    }

    private function _workTimeProcess($workorders)
    {
        $totalTime = 0;
        $totalDays = 0;
        $totalHours = 0;
        $totalMinutes = 0;
        $machineDatas = [];

        if ($workorders != null) {
            foreach ($workorders as $workorder) {
                $machines = $workorder->getMachines();
                foreach ($machines as $machine) {
                    $machineId = $machine->getId();
                    $machineName = $machine->getDesignation();
                }

                if (isset($machineDatas[$machineId])) {
                    $machineDatas[$machineId]['BT']++;
                    $machineDatas[$machineId]['days'] += $workorder->getDurationDay();
                    $machineDatas[$machineId]['hours'] += $workorder->getDurationHour();
                    $machineDatas[$machineId]['minutes'] += $workorder->getDurationMinute();

                    $machineDatas[$machineId] = $this->_manageTime($machineDatas[$machineId]);
                } else {
                    $machineDatas[$machineId] = [
                    'id' => $machineId,
                    'name' => $machineName,
                    'BT' => 1,
                    'days' => $workorder->getDurationDay(),
                    'hours' => $workorder->getDurationHour(),
                    'minutes' => $workorder->getDurationMinute(),
                    ];
                    $machineDatas[$machineId] = $this->_manageTime($machineDatas[$machineId]);
                }

                $totalTime += $machineDatas[$machineId]['totalMinutes'];
            }

            $totalHours = (int)floor($totalTime / 60) % 24;
            $totalMinutes = $totalTime % 60;
            $totalDays = (int)floor($totalTime / (24 * 60));

            array_multisort(array_column($machineDatas, 'totalMinutes'), SORT_DESC, $machineDatas);
        }

        return [
        'machineDatas' => $machineDatas,
        'totalDays' => $totalDays,
        'totalHours' => $totalHours,
        'totalMinutes' => $totalMinutes,
        ];
    }


    #[Route('/indicator/machineCost', name: 'app_machine_cost')]
    #[IsGranted('ROLE_USER')]
    public function machineCost(Request $request): Response
    {

        $searchIndicator = new SearchIndicator();
        $workorders = $this->_readWorkorders($searchIndicator);

        $machineDatas = $this->_processWorkorders($workorders);

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator);

            $machineDatas = $this->_processWorkorders($workorders);
        }

        $totalPartsPrice = 0;
        if ($machineDatas) {
            foreach ($machineDatas as $machineData) {
                $totalPartsPrice = $totalPartsPrice + $machineData['partsPrice'];
            }
        }

        return $this->render(
            'indicator/machineCost.html.twig', [
            'form' => $form->createView(),
            'machineDatas' => $machineDatas,
            'totalPartsPrice' => $totalPartsPrice,
            ]
        );
    }

    private function _processWorkorders($workorders)
    {
        if ($workorders != null) {
            $machineDatas = [];
            foreach ($workorders as $workorder) {
                $machines = $workorder->getMachines();
                foreach ($machines as $machine) {
                    $machineId = $machine->getId();
                    if (isset($machineDatas[$machineId])) {
                        $machineDatas[$machineId]['BT'] 
                            = ++$machineDatas[$machineId]['BT'];

                        $machineDatas[$machineId]['partsPrice'] 
                            = $machineDatas[$machineId]['partsPrice'] 
                            + $workorder->getPartsPrice();

                    } else {
                        $machineDatas[$machineId] = [
                            'id' => $machineId,
                            'name' => $machine->getDesignation(),
                            'BT' => 1,
                            'partsPrice' => $workorder->getPartsPrice(),
                        ];
                    }
                }
            }
            // Sort of machineDatas array
            $columns = array_column($machineDatas, 'partsPrice');
            array_multisort($columns, SORT_DESC, $machineDatas);

            // Suppression des machines à 0€
            $index = 0;
            foreach ($machineDatas as $machineData) {
                if ($machineData['partsPrice'] <= 0) {
                    unset($machineDatas[$index]);
                }
                $index++;
            }
            return $machineDatas;
        }
        return;
    }

    #[Route('/indicator/costPerMonth', name: 'app_cost_per_month')]
    #[IsGranted('ROLE_USER')]
    public function costPerMonth(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();
        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        $months = [];
        $values = [];

        $workorders = $this->_readWorkorders($searchIndicator);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator);
        }

        if (!empty($workorders)) {
            $datas = [];

            foreach ($workorders as $workorder) {
                $month = $workorder->getStartDate()->format('m');
                $year = $workorder->getStartDate()->format('y');
                $key = "$year-$month";

                if (!isset($datas[$key])) {
                    $datas[$key] = [
                    'monthName' => $month,
                    'year' => $year,
                    'value' => 0
                    ];
                }

                $datas[$key]['value'] += $workorder->getPartsPrice();
            }

            ksort($datas);

            foreach ($datas as $data) {
                $months[] = $data['monthName'] . "/" . $data['year'];
                $values[] = $data['value'];
            }
        }
        
        return $this->render(
            'indicator/costPerMonth.html.twig', [
            'form' => $form->createView(),
            'months' => json_encode($months ?? []),
            'values' => json_encode($values ?? []),
            ]
        );
    }

    private function _manageTime($datas)
    {
        if ($datas['minutes'] >= 59) {
            $minutes = $datas['minutes'] % 60;
            $hours = (int)floor($datas['minutes'] / 60);
            $datas['minutes'] = $minutes;
            $datas['hours'] = $datas['hours'] + $hours;
        }

        if ($datas['hours'] >= 24) {
            $hours = $datas['hours'] % 24;
            $days = (int)floor($datas['hours'] / 24);
            $datas['hours'] = $hours;
            $datas['days'] = $datas['days'] + $days;
        }

        // Count of total minutes/workorder
        $datas['totalMinutes'] 
            = ($datas['days'] * 24 * 60)
            + ($datas['hours'] * 60)
            + ($datas['minutes']);

        return $datas;
    }

    #[Route('/indicator/curatifPreventif', name: 'app_cur_prev')]
    #[IsGranted('ROLE_USER')]
    public function curatifVsPreventif(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();
        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        $workorders = $this->_readWorkorders($searchIndicator);

        $preventive = [];
        $curative = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator);
        }

        if (!empty($workorders)) {
            foreach ($workorders as $workorder) {
                $monthKey = $workorder->getStartDate()->format('y/m');
                $duration = $this->_manageCuraPrevTime($workorder);

                if ($workorder->getPreventive()) {
                    $preventive[$monthKey] = ($preventive[$monthKey] ?? 0) + $duration;
                } else {
                    $curative[$monthKey] = ($curative[$monthKey] ?? 0) + $duration;
                }
            }

            // Fusion des mois pour avoir un tableau commun (au cas où l’un des deux types est absent pour un mois)
            $allMonths = array_unique(array_merge(array_keys($preventive), array_keys($curative)));
            sort($allMonths); // Tri croissant

            $months = [];
            $valuesPreventive = [];
            $valuesCurative = [];

            foreach ($allMonths as $month) {
                $months[] = substr($month, 3) . '/' . substr($month, 0, 2); // "y/m" -> "m/y"
                $valuesPreventive[] = $preventive[$month] ?? 0;
                $valuesCurative[] = $curative[$month] ?? 0;
            }

            return $this->render(
                'indicator/preventiveVSCurative.html.twig', [
                'form' => $form->createView(),
                'months' => json_encode($months),
                'valuesPreventive' => json_encode($valuesPreventive),
                'valuesCurative' => json_encode($valuesCurative),
                ]
            );
        }

        // Aucun workorder
        return $this->render(
            'indicator/preventiveVSCurative.html.twig', [
            'form' => $form->createView(),
            'months' => null,
            'valuesPreventive' => null,
            'valuesCurative' => null,
            ]
        );
    }


    private function _manageCuraPrevTime($data)
    {
        $minutes = $data->getDurationMinute();
        $hours = $data->getDurationHour();
        $days = $data->getDurationDay();
        $minutes = ($hours * 60) + ($days * 24 * 60);

        return $minutes;
    }
}
