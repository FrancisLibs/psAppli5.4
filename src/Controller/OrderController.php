<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Data\SearchOrder;
use App\Form\SearchOrderFormType;
use App\Repository\OrderRepository;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    protected OrderRepository $orderRepository;
    protected EntityManagerInterface $manager;
    protected OrganisationService $organisation;

    public function __construct(
        OrganisationService $organisation,
        OrderRepository $orderRepository,
        EntityManagerInterface $manager
    ) {
        $this->orderRepository = $orderRepository;
        $this->manager = $manager;
        $this->organisation = $organisation;
    }

    /**
     * Liste des  commande
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/list', name: 'order_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $data = new SearchOrder();
        $data->organisation = $organisation;
        $data->page = $request->get('page', 1);

        $form = $this->createForm(SearchOrderFormType::class, $data);
        $form->handleRequest($request);

        $orders = $this->orderRepository->findSearch($data);

        // Gestion AJAX (mise à jour partielle)
        if ($request->query->getBoolean('ajax')) {
            return new JsonResponse(
                [
                'content' => $this->renderView(
                    'order/_orders.html.twig', [
                    'orders' => $orders
                    ]
                ),
                'sorting' => $this->renderView(
                    'order/_sorting.html.twig', [
                    'orders' => $orders
                    ]
                ),
                'pagination' => $this->renderView(
                    'order/_pagination.html.twig', [
                    'orders' => $orders
                    ]
                ),
                ]
            );
        }

        return $this->render(
            'order/index.html.twig', [
            'orders' => $orders,
            'form' => $form->createView(),
            ]
        );
    }
    /**
    * Nouvelle commande
    */
    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'order_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $organisation = $this->organisation->getOrganisation();

        $order = new Order();
        $order->setOrganisation($organisation);
        $order->setDate(new \DateTime());
        $order->setStatus('pending');
        $order->setNumber($this->orderRepository->getNextNumber());
        $order->setCreatedBy($this->getUser());

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setNumber(str_pad($order->getNumber(), 3, "0", STR_PAD_LEFT));
            $this->manager->persist($order);
            $this->manager->flush();

            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        return $this->renderForm(
            'order/new.html.twig', [
            'form' => $form,
            ]
        );
    }

    /**
     * Visualisation d'une commande
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/show/{id}', name: 'order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render(
            'order/show.html.twig', [
            'order' => $order,
            ]
        );
    }

    /**
     * Édition d'une commande
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/edit/{id}', name: 'order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->manager->flush();

            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        return $this->renderForm(
            'order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
            ]
        );
    }

    /**
     * Suppression d'une commande
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/delete/{id}', name: 'order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $this->manager->remove($order);
            $this->manager->flush();

            $this->addFlash('success', 'Commande supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Échec de la suppression : token CSRF invalide.');
        }

        return $this->redirectToRoute('order_index');
    }
}
