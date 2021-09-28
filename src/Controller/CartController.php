<?php

namespace App\Controller;

use App\Data\SearchPart;
use App\Entity\Workorder;
use App\Form\SearchPartForm;
use App\Repository\PartRepository;
use PhpParser\Node\Stmt\Foreach_;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    private $partRepository;
    private $requestStack;

    public function __construct(PartRepository $partRepository, RequestStack $requestStack)
    {
        $this->partRepository = $partRepository;
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/cart/{workorderId}", name="cart_index")
     */
    public function index($workorderId): Response
    {
        $session = $this->requestStack->getSession();

        $panier = $session->get('panier', []);

        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'part' => $this->partRepository->find($id),
                'quantity' => $quantity,
            ];
        }

        return $this->render('cart/index.html.twig', [
            'items' => $panierWithData,
            'workorderId' => $workorderId,
        ]);
    }

    /**
     * @Route("/cart/add/{id}/{workorderId}", name="cart_add")
     */
    public function add($id, $workorderId): Response
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $session->set('panier', $panier);

        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'part' => $this->partRepository->find($id),
                'quantity' => $quantity,
            ];
        }

        $total = 0;
        foreach ($panierWithData as $item) {
            $total += $item['quantity'];
        }

        return $this->redirectToRoute('add_part', [
            'id' => $workorderId,
        ]);
    }

    /**
     * @Route("/cart/remove/{id}/{workorderId}", name="cart_remove")
     */
    public function remove($id, $workorderId): Response
    {
        $session = $this->requestStack->getSession();

        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('add_part', [
            'id' => $workorderId,
        ]);
    }

    /**
     * @Route("/{id}/add_part/", name="add_part", methods={"GET"})
     */
    public function addPart(Workorder $workorder, Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'part' => $this->partRepository->find($id),
                'quantity' => $quantity,
            ];
        }

        $user = $this->getUser();
        $organisation = $user->getOrganisation();

        $data = new SearchPart();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchPartForm::class, $data);
        $form->remove('organisation');

        $form->handleRequest($request);
        $parts = $this->partRepository->findSearch($data);

        return $this->render('workorder/addPart.html.twig', [
            'addPart' => true,
            'parts' =>  $parts,
            'form'  =>   $form->createView(),
            'workorderId' => $workorder->getId(),
            'items' => $panierWithData,
        ]);
    }

    /**
     * @Route("/cart/empty/{workorderId}", name="cart_empty")
     */
    public function empty($workorderId): Response
    {
        $session = $this->requestStack->getSession();

        $panier = $session->get('panier', []);

        foreach ($panier as $id => $qte) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('add_part', [
            'id' => $workorderId,
        ]);
    }
}
