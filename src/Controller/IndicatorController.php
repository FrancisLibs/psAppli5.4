<?php

namespace App\Controller;

use App\Data\SearchIndicator;
use App\Form\SearchIndicatorType;
use App\Repository\WorkorderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndicatorController extends AbstractController
{
    private $workorderRepository;

    public function __construct(WorkorderRepository $workorderRepository)
    {
        $this->workorderRepository = $workorderRepository;
    }

    private function readWorkorders($searchIndicator)
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();

        if(empty($searchIndicator->startDate)){
            $searchIndicator->startDate = new \DateTime('2022/01/01');
            $searchIndicator->endDate = new \DateTime('2023/12/31');
        };

        $workorders = $this->workorderRepository->findIndicatorsWorkorders($organisation, $searchIndicator);

        return $workorders;
    }


    #[Route('/indicator/workTime', name: 'app_work_time')]
    public function workTime(Request $request): Response
    {        
        $searchIndicator = new SearchIndicator();
        
        // $searchIndicator = null;
        
        $workorders = $this->readWorkorders($searchIndicator);

        $datas = $this->workTimeProcess($workorders);

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->readWorkorders($searchIndicator);
            if ($workorders) {
                $datas = $this->workTimeProcess($workorders);

                return $this->render('indicator/workorderTimes.html.twig', [
                    'form' => $form->createView(),
                    'machineDatas' => $datas['machineDatas'],
                    'totalDays' => $datas['totalDays'],
                    'totalHours' => $datas['totalHours'],
                    'totalMinutes' => $datas['totalMinutes']
                ]);
            }

            return $this->render('indicator/workorderTimes.html.twig', [
                'form' => $form->createView(),
                'totalDays' => $datas['totalDays'],
                'totalHours' => $datas['totalHours'],
                'totalMinutes' => $datas['totalMinutes']
            ]);
        }

        return $this->render('indicator/workorderTimes.html.twig', [
            'form' => $form->createView(),
            'machineDatas' => $datas['machineDatas'],
            'totalDays' => $datas['totalDays'],
            'totalHours' => $datas['totalHours'],
            'totalMinutes' => $datas['totalMinutes']
        ]);
    }

    private function workTimeProcess($workorders)
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
        $workorders = $this->readWorkorders($searchIndicator);
        
        $machineDatas = $this->processWorkorders($workorders);

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->readWorkorders($searchIndicator);
            
            $machineDatas = $this->processWorkorders($workorders);  
        }

        $totalPartsPrice=0;
        if($machineDatas){
            foreach($machineDatas as $machineData){
                $totalPartsPrice = $totalPartsPrice + $machineData['partsPrice'];
            }

        }
        

        return $this->render('indicator/machineCost.html.twig', [
            'form' => $form->createView(),
            'machineDatas' => $machineDatas,
            'totalPartsPrice' => $totalPartsPrice,
        ]);
    }

    private function processWorkorders($workorders)
    {
        if ($workorders != null){
            
            $machineDatas = [];

            foreach ($workorders as $workorder) {

                $machines = $workorder->getMachines();

                foreach ($machines as $machine) {

                    $machineId = $machine->getId();

                    if (isset($machineDatas[$machineId])) {
                        $machineDatas[$machineId]['BT'] = ++$machineDatas[$machineId]['BT'];
                        $machineDatas[$machineId]['partsPrice'] = $machineDatas[$machineId]['partsPrice'] + $workorder->getPartsPrice();
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
            foreach($machineDatas as $machineData){
                if( $machineData['partsPrice'] <= 0 ) {
                    unset ($machineDatas[$index]);
                }
                $index ++;
            }

            return $machineDatas;
        }
        return;
    }

    #[Route('/indicator/costPerMonth', name: 'app_cost_per_month')]
    public function costPerMonth(Request $request): Response
    {
        $searchIndicator = new SearchIndicator();

        $workorders = $this->readWorkorders($searchIndicator);

        $form = $this->createForm(SearchIndicatorType::class, $searchIndicator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorders = $this->readWorkorders($searchIndicator);
        }

        $datas= array();

        if ($workorders != null) {
            foreach ($workorders as $workorder) {

                $workorderDateMonth = $workorder->getStartDate()->format('m');
                $workorderDateYear = (int)$workorder->getStartDate()->format('y');
                $monthNumber = $workorderDateYear. "-". $workorderDateMonth . "-01";

                $workorderPartsValue = $workorder->getPartsPrice();

                if (array_key_exists($monthNumber, $datas)) {
                    $datas[$monthNumber]['value'] = $datas[$monthNumber]['value'] + $workorderPartsValue;
                } else {
                    $datas[$monthNumber]['number']= $monthNumber;
                    $datas[$monthNumber]['monthName'] = $workorderDateMonth;
                    $datas[$monthNumber]['year'] = $workorderDateYear;
                    $datas[$monthNumber]['value'] = $workorderPartsValue;
                }
            }

            array_multisort($datas, SORT_ASC, SORT_REGULAR);

            $index = 0;
            foreach($datas as $data) {
                $months[$index] = $data['monthName']. "/" . $data['year'];
                //dump($data['monthName']);
                $values[$index] = $data['value'];
                $index++;
            }

            return $this->render('indicator/costPerMonth.html.twig', [
                'form' => $form->createView(),
                'months' =>  json_encode($months),
                'values' => json_encode($values),
            ]);
        }

        return $this->render('indicator/costPerMonth.html.twig', [
                'form' => $form->createView(),
                'months' =>  null,
                'values' => null,
            ]);
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
