<?php

namespace App\Controller;

use App\Repository\WorkOrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, WorkOrderRepository $workOrderRepository,PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $workOrders = $workOrderRepository->findAll();
        $workOrders = $paginator->paginate(
            $workOrders, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

        return $this->render('default/index.html.twig', [
            'workOrders'    => $workOrders,
        ]);
    }
}
