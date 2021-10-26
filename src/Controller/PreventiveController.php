<?php

namespace App\Controller;

use App\Data\SearchPreventive;
use App\Form\SearchPreventiveForm;
use App\Repository\ScheduleRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/preventive")
 */
class PreventiveController extends AbstractController
{
    private $manager;
    private $workorderRepository;
    private $scheduleRepository;

    public function __construct(
        EntityManagerInterface $manager,
        WorkorderRepository $workorderRepository,
        ScheduleRepository $scheduleRepository
    ) {
        $this->manager = $manager;
        $this->workorderRepository = $workorderRepository;
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * @Route("/", name="preventive_index", methods={"GET"})
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
}
