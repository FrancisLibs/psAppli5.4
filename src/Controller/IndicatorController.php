<?php

namespace App\Controller;

use App\Repository\WorkorderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndicatorController extends AbstractController
{
    private $workorderRepository;

    public function __construct(WorkorderRepository $workorderRepository)
    {
        $this->workorderRepository = $workorderRepository;
    }

    #[Route('/indicator/workTime', name: 'app_work_time')]
    public function workTime(): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $year = '2023-01-01';
        $totalTime = 0;
        $totalDays = 0;
        $totalHours = 0;
        $totalMinutes = 0;

        $workorders = $this->workorderRepository->findIndicatorsWorkorders($organisation, $year);
        if ($workorders != null) {
            $machineDatas = [];
            foreach ($workorders as $workorder) {
                $machines = $workorder->getMachines();
                foreach ($machines as $machine) {
                    $machineId = $machine->getId();
                    $machineName = $machine->getDesignation();
                };
                if (isset($machineDatas[$machineId])) {
                    $machineDatas[$machineId]['BT'] = ++$machineDatas[$machineId]['BT'];
                    $machineDatas[$machineId]['days'] = $machineDatas[$machineId]['days'] + $workorder->getDurationDay();
                    $machineDatas[$machineId]['hours'] = $machineDatas[$machineId]['hours'] + $workorder->getDurationHour();
                    $machineDatas[$machineId]['minutes'] = $machineDatas[$machineId]['minutes'] + $workorder->getDurationMinute();
                    $machineDatas[$machineId] = $this->manageTime($machineDatas[$machineId]);
                } else {
                    $machineDatas[$machineId] = [
                        'id' => $machineId,
                        'name' => $machineName,
                        'BT' => 1,
                        'days' => $workorder->getDurationDay(),
                        'hours' => $workorder->getDurationHour(),
                        'minutes' => $workorder->getDurationMinute(),

                    ];
                    $machineDatas[$machineId] = $this->manageTime($machineDatas[$machineId]);
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
        }

        return $this->render('indicator/workorderTimes.html.twig', [
            'machineDatas' => $machineDatas,
            'totalDays' => $totalDays,
            'totalHours' => $totalHours,
            'totalMinutes' => $totalMinutes
        ]);
    }

    #[Route('/indicator/machineCost', name: 'app_machine_cost')]
    public function machineCost(): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $year = '2023-01-01';
        $totalPartsPrice = 0;

        $workorders = $this->workorderRepository->findIndicatorsWorkorders($organisation, $year);
        if ($workorders != null) {
            $machineDatas = [];

            foreach ($workorders as $workorder) {
                $machines = $workorder->getMachines();

                foreach ($machines as $machine) {
                    $machineId = $machine->getId();
                    $machineName = $machine->getDesignation();
                };

                if (isset($machineDatas[$machineId])) {
                    $machineDatas[$machineId]['BT'] = ++$machineDatas[$machineId]['BT'];
                    $machineDatas[$machineId]['partsPrice'] = $machineDatas[$machineId]['partsPrice'] + $workorder->getPartsPrice();
                } else {
                    $machineDatas[$machineId] = [
                        'id' => $machineId,
                        'name' => $machineName,
                        'BT' => 1,
                        'partsPrice' => $workorder->getPartsPrice(),
                    ];
                }
                $totalPartsPrice = $totalPartsPrice + $machineDatas[$machineId]['partsPrice'];
            }
        }
        // Sort of machineDatas array
        $columns = array_column($machineDatas, 'partsPrice');
        array_multisort($columns, SORT_DESC, $machineDatas);

        return $this->render('indicator/machineCost.html.twig', [
            'machineDatas' => $machineDatas,
            'totalPartsPrice' => $totalPartsPrice,
        ]);
    }

    #[Route('/indicator/costPerMonth', name: 'app_cost_per_month')]
    public function costPerMonth(): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $year = '2023-01-01';
        $datas[] = 0;
        $months = array(
            0 => " janvier ", " février ", " mars ", " avril ", " mai ", " juin ",
            " juillet ", " août ", " septembre ", " octobre ", " novembre ", " décembre "
        );
        $values = array(
            0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0, 11 => 0, 12 => 0
        );

        $workorders = $this->workorderRepository->findIndicatorsWorkorders($organisation, $year);
        if ($workorders != null) {
            foreach ($workorders as $workorder) {
                $workorderDate = $workorder->getCreatedAT();
                $workorderPartsValue = $workorder->getPartsPrice();
                $monthNumber = $this->month($workorderDate)-1;

                if (array_key_exists($monthNumber, $values)) {
                    $values[$monthNumber] = $values[$monthNumber] + $workorderPartsValue;
                } else {
                    $values[$monthNumber] = $workorderPartsValue;
                }
            }
        }

        return $this->render('indicator/costPerMonth.html.twig', [
            'months' =>  json_encode($months),
            'values' => json_encode($values),
        ]);
    }

    private function month($date)
    {
        $date = getdate($date->getTimestamp());
        $month = (int)$date['mon'];

        return $month;
    }

    private function manageTime($machineDatas)
    {
        if ($machineDatas['minutes'] >= 59) {
            $minutes = $machineDatas['minutes'] % 60;
            $hours = (int)floor($machineDatas['minutes'] / 60);
            $machineDatas['minutes'] = $minutes;
            $machineDatas['hours'] = $machineDatas['hours'] + $hours;
        }

        if ($machineDatas['hours'] >= 24) {
            $hours = $machineDatas['hours'] % 24;
            $days = (int)floor($machineDatas['hours'] / 24);
            $machineDatas['hours'] = $hours;
            $machineDatas['days'] = $machineDatas['days'] + $days;
        }

        // Count of total minutes/workorder
        $machineDatas['totalMinutes'] =
            ($machineDatas['days'] * 24 * 60)
            + ($machineDatas['hours'] * 60)
            + ($machineDatas['minutes']);

        return $machineDatas;
    }
}
