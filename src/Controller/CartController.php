<?php

namespace App\Controller;

use App\Entity\Part;
use App\Data\SearchPart;
use App\Entity\Workorder;
use App\Form\SearchPartForm;
use App\Entity\WorkorderPart;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/cart")
 */

class CartController extends AbstractController
{
    private $partRepository;
    private $requestStack;
    private $manager;

    public function __construct(PartRepository $partRepository, RequestStack $requestStack, EntityManagerInterface $manager)
    {
        $this->partRepository = $partRepository;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Appel de la liste des pièces à ajouter au panier
     * 
     * @Route("/add_part/{id}", name="add_part", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * @param   Workorder $workorder
     * @param   Request $request 
     * @return  Response
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
        
        $data = $session->get('data', null);
        
        if (!$data) {
            $data = new SearchPart();
        }
        $data->organisation = $organisation;
        
        $form = $this->createForm(SearchPartForm::class, $data);
        
        $form->remove('organisation');

        $form->handleRequest($request);
        $session->set('data', $data);
        $parts = $this->partRepository->findSearch($data);
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('part/_partsAdd.html.twig', [
                    'parts' => $parts,
                    'addPart' => true,
                    'workorderId' => $workorder->getId(),
                ]),
                'sorting'       =>  $this->renderView('part/_sortingAdd.html.twig', ['parts' => $parts]),
                'pagination'    =>  $this->renderView('part/_pagination.html.twig', ['parts' => $parts]),
            ]);
        }

        return $this->render('workorder/addPart.html.twig', [
            'addPart' => true,
            'parts' =>  $parts,
            'form'  =>   $form->createView(),
            'workorderId' => $workorder->getId(),
            'items' => $panierWithData,
        ]);
    }

    /**
     * Liste des pièces dans le panier
     * 
     * @Route("/cart/{workorderId}", name="cart_index")
     * @Security("is_granted('ROLE_USER')")
     * @param   workorderId
     * @return  response
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
     * Ajoute une pièce dans le panier
     * 
     * @Route("/cart/add/{id}/{workorderId}", name="cart_add")
     * @Security("is_granted('ROLE_USER')")
     * @param   id              $id de la pièce ajoutée
     * @param   workorderId     id du workorder
     * @return redirectResponse
     */
    public function add(Part $part, $workorderId): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        $id = $part->getId();

        // Quantité dans le stock de pièces
        $qteStock = $part->getStock()->getQteStock();

        // Quantité dans le panier
        if (!empty($panier[$id])) {
            $qteCart = $panier[$id];
        } else {
            $qteCart = 0;
        }

        // Test si selon la quantité disponible, il est possible de mettre la pièce dans le panier
        if ($qteStock > 0 && $qteStock > $qteCart) {

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
        }
        return $this->redirectToRoute('add_part', [
            'id' => $workorderId,
        ]);
    }

    /**
     * Enlève une pièce du panier
     * @Security("is_granted('ROLE_USER')")
     * @Route("/cart/remove/{id}/{workorderId}", name="cart_remove")
     * @Security("is_granted('ROLE_USER')")
     * @param   id              id de la pièce à enlever
     * @param   workorderId     id du workorder
     * @return  RedirectResponse
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
     * Vidange du panier
     * 
     * @Route("/cart/empty/{workorderId}", name="cart_empty")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param workorderId   id du panier
     * @return RedirectResponse
     */
    public function empty($workorderId): RedirectResponse
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

    /**
     * Validation du panier
     * 
     * @Route("/cart/validation/{id}", name="cart_valid")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param workorderId   id du panier
     * @return RedirectResponse
     */
    public function valid(Workorder $workorder): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        // Affection des pièces du panier au bt
        foreach ($panier as $id => $qte) {

            // Ajout de la pièce au bt
            $workorderPart = new WorkorderPart();
            $part = $this->partRepository->find($id);
            $workorderPart->setPart($part);
            $workorderPart->setQuantity($qte);
            $workorder->addWorkorderPart($workorderPart);

            // Ajout de la pièce à la machine
            $machines = $workorder->getMachines();
            $parts = $machines->first()->getParts();
            if (!$parts->contains($part)) {
                $machines->first()->addPart($part);
            }

            // Rectification de la quantité de pièce en stock
            $part->getStock()->setQteStock($part->getStock()->getQteStock() - $qte);
            $this->manager->persist($workorder);
            $this->manager->persist($workorderPart);
        }
        $this->manager->flush();

        // Effacement du panier
        foreach ($panier as $id => $qte) {
            unset($panier[$id]);
        }

        //Sauvegarde du panier 
        $session->set('panier', $panier);

        return $this->redirectToRoute('work_order_show', [
            'id' => $workorder->getId(),
        ]);
    }
}
