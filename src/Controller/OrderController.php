<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Data\SearchOrder;
use App\Form\SearchOrderFormType;
use App\Repository\OrderRepository;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AccountTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/order")
 */

class OrderController extends AbstractController
{
    protected $orderRepository;
    protected $accountTypeRepository;
    protected $manager;
    protected $organisation;

    public function __construct(
        OrganisationService $organisation,
        OrderRepository $orderRepository,
        EntityManagerInterface $manager,
        AccountTypeRepository $accountTypeRepository,
    ) {
        $this->orderRepository = $orderRepository;
        $this->manager = $manager;
        $this->organisation = $organisation;
        $this->accountTypeRepository = $accountTypeRepository;
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/list', name: 'order_index', methods: ('GET'))]
    public function index(Request $request): Response
    {
        $organisation =  $this->organisation->getOrganisation();
        
        $data = new SearchOrder();

        $data->organisation = $organisation;

        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchOrderFormType::class, $data);
        
        $form->handleRequest($request);
        $orders = $this->orderRepository->findSearch($data);
        
        if ($request->get('ajax') == 1) {
            return new JsonResponse(
                [
                    'content'       =>  $this->renderView(
                        'order/_orders.html.twig',
                        ['orders' => $orders]
                    ),
                    'sorting'       =>  $this->renderView(
                        'order/_sorting.html.twig',
                        ['orders' => $orders]
                    ),
                    'pagination'    =>  $this->renderView(
                        'order/_pagination.html.twig',
                        ['orders' => $orders]
                    ),
                ]
            );
        }
        
        return $this->render(
            'order/index.html.twig',
            [
                'orders' =>  $orders,
                'form'  =>  $form->createView(),
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'order_new', methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $order = new Order();
        $order->setOrganisation($organisation);
        $order->setDate(new \DateTime());
        $order->setStatus('pending');
        $order->setNumber($this->orderRepository->getNextNumber());
        $order->setCreatedBy($this->getUser());
        $accountType = $this->accountTypeRepository->findOneBy(['letter' => 'D']);
        $order->addAccountType($accountType);

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($order);
            $this->manager->flush();

            $this->addFlash('success', 'Order created successfully.');
            return $this->redirectToRoute('order_index');
        }
        return $this->renderForm(
            'order/new.html.twig',
            ['form' => $form]
        );
    }

    /**
     * @ Visualisation commande
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/show/{id}', name: 'order_show', methods: ["GET"])]
    public function show(Order $order): Response
    {
        return $this->render(
            'order/show.html.twig',
            ['order' => $order]
        );
    }
}


