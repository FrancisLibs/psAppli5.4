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
            $searchIndicator->startDate = new \DateTime('2022/01/01');
            $searchIndicator->endDate = new \DateTime('2023/12/31');
        };

        $workorders = $this->_workorderRepository->findIndicatorsWorkorders(
            $organisation, 
            $searchIndicator
        );

        return $workorders;
    }


    #[Route('/indicator/workTime', name: 'app_work_time')]
    public function workTime(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();

        // $searchIndicator = null;

        $workorders = $this->_readWorkorders($searchIndicator);

        $datas = $this->_workTimeProcess($workorders);

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
                };
                if (isset($machineDatas[$machineId])) {
                    $machineDatas[$machineId]['BT'] 
                        = ++$machineDatas[$machineId]['BT'];

                    $machineDatas[$machineId]['days'] 
                        = $machineDatas[$machineId]['days'] 
                        + $workorder->getDurationDay();

                    $machineDatas[$machineId]['hours'] 
                        = $machineDatas[$machineId]['hours'] 
                        + $workorder->getDurationHour();

                    $machineDatas[$machineId]['minutes'] 
                        = $machineDatas[$machineId]['minutes'] 
                        + $workorder->getDurationMinute();

                    $machineDatas[$machineId] 
                        = $this->_manageTime($machineDatas[$machineId]);

                } else {
                    $machineDatas[$machineId] = [
                        'id' => $machineId,
                        'name' => $machineName,
                        'BT' => 1,
                        'days' => $workorder->getDurationDay(),
                        'hours' => $workorder->getDurationHour(),
                        'minutes' => $workorder->getDurationMinute(),

                    ];
                    $machineDatas[$machineId] = $this->_manageTime(
                        $machineDatas[$machineId]
                    );
                }
                // total time in minutes
                $totalTime = $totalTime + $machineDatas[$machineId]['totalMinutes'];
            }

            // compute of the values in days, hours and minutes
            $totalHours = (int)floor($totalTime / 60);
            $totalMinutes = $totalTime - ($totalHours * 60);
            $totalHours = $totalHours % 24;
            $totalDays = (int)floor($totalTime / (24 * 60));

            // Sort of machineDatas array
            $columns = array_column($machineDatas, 'totalMinutes');
            array_multisort($columns, SORT_DESC, $machineDatas);

            $datas = [
                'machineDatas' => $machineDatas,
                'totalDays' => $totalDays,
                'totalHours' => $totalHours,
                'totalMinutes' => $totalMinutes,
            ];
            return $datas;
        }

        return;
    }

    #[Route('/indicator/machineCost', name: 'app_machine_cost')]
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
    public function costPerMonth(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();
        $workorders = $this->_readWorkorders($searchIndicator);
        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator);
        }

        $datas = array();

        if ($workorders != null) {
            foreach ($workorders as $workorder) {
                $workorderDateMonth = $workorder->getStartDate()->format('m');

                $workorderDateYear = (int)$workorder->getStartDate()->format('y');

                $monthNumber 
                    = $workorderDateYear . "-" . $workorderDateMonth . "-01";

                $workorderPartsValue = $workorder->getPartsPrice();

                if (array_key_exists($monthNumber, $datas)) {
                    $datas[$monthNumber]['value'] 
                        = $datas[$monthNumber]['value'] + $workorderPartsValue;
                } else {
                    $datas[$monthNumber]['number'] = $monthNumber;
                    $datas[$monthNumber]['monthName'] = $workorderDateMonth;
                    $datas[$monthNumber]['year'] = $workorderDateYear;
                    $datas[$monthNumber]['value'] = $workorderPartsValue;
                }
            }

            array_multisort($datas, SORT_ASC, SORT_REGULAR);

            $index = 0;
            foreach ($datas as $data) {
                $months[$index] = $data['monthName'] . "/" . $data['year'];
                $values[$index] = $data['value'];
                $index++;
            }

            return $this->render(
                'indicator/costPerMonth.html.twig', [
                'form' => $form->createView(),
                'months' =>  json_encode($months),
                'values' => json_encode($values),
                ]
            );
        }

        return $this->render(
            'indicator/costPerMonth.html.twig', [
            'form' => $form->createView(),
            'months' =>  null,
            'values' => null,
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
    public function curatifVsPreventif(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();
        $workorders = $this->_readWorkorders($searchIndicator);
        $preventive = [];
        $curative = [];

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->_readWorkorders($searchIndicator);
        }

        if ($workorders != null) {
            foreach ($workorders as $workorder) {
                $workorderDateMonth = $workorder->getStartDate()->format('m');
                $workorderDateYear = (int)$workorder->getStartDate()->format('y');
                $monthNumber = $workorderDateYear . "/" . $workorderDateMonth;

                if ($workorder->getPreventive()) {
                    if (array_key_exists($monthNumber, $preventive)) {
                        $preventive[$monthNumber] 
                        += $this->_manageCuraPrevTime($workorder);
                    } else {
                        $preventive[$monthNumber] 
                            = $this->_manageCuraPrevTime($workorder);
                    }
                } else {
                    if (array_key_exists($monthNumber, $curative)) {
                        $curative[$monthNumber] 
                            += $this->_manageCuraPrevTime($workorder);
                    } else {
                        $curative[$monthNumber] 
                            = $this->_manageCuraPrevTime($workorder);
                    }
                }
            }

            // Sort of the array
            $columns = array_keys($preventive);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $preventive);

            // Sort of the array
            $columns = array_keys($curative);
            array_multisort($columns, SORT_ASC, SORT_REGULAR, $curative);

            $index = 0;
            $months=[];
            foreach ($preventive as $key => $value) {
                $months[$index] = $key;
                $valuesPreventive[$index] = $value;
                $index++;
            }

            $index = 0;
            foreach ($curative as $key => $value) {
                $valuesCurative[$index] = $value;
                $index++;
            }

            foreach ($months as $month) {
                $month =  substr($month, 3) . "/" . substr($month, 0, 2);
            }

            return $this->render(
                'indicator/preventiveVSCurative.html.twig', [
                'form' => $form->createView(),
                'months' => json_encode($months),
                'valuesPreventive' =>  json_encode($valuesPreventive),
                'valuesCurative' => json_encode($valuesCurative),
                ]
            );
        }

        return $this->render(
            'indicator/costPerMonth.html.twig', [
            'form' => $form->createView(),
            'months' =>  null,
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
