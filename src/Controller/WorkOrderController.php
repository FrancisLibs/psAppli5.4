<?php

namespace App\Controller;

use App\Entity\WorkOrder;
use App\Form\WorkOrderType;
use App\Repository\WorkOrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/work/order")
 */
class WorkOrderController extends AbstractController
{
    /**
     * @Route("/", name="work_order_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator, WorkOrderRepository $workOrderRepository): Response
    {
        $workOrders = $workOrderRepository->findAll();

        $workOrders = $paginator->paginate(
            $workOrders, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('work_order/index.html.twig', [
            'workOrders'    =>  $workOrders,
        ]);
    }

    /**
     * @Route("/new", name="work_order_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $workOrder = new WorkOrder();
        $form = $this->createForm(WorkOrderType::class, $workOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($workOrder);
            $entityManager->flush();

            return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('work_order/new.html.twig', [
            'work_order' => $workOrder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="work_order_show", methods={"GET"})
     */
    public function show(WorkOrder $workOrder): Response
    {
        return $this->render('work_order/show.html.twig', [
            'work_order' => $workOrder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="work_order_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, WorkOrder $workOrder): Response
    {
        $form = $this->createForm(WorkOrderType::class, $workOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('work_order/edit.html.twig', [
            'work_order' => $workOrder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="work_order_delete", methods={"POST"})
     */
    public function delete(Request $request, WorkOrder $workOrder): Response
    {
        if ($this->isCsrfTokenValid('delete'.$workOrder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($workOrder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
