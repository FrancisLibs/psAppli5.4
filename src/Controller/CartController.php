<?php

namespace App\Controller;

use App\Entity\Part;
use App\Entity\Workorder;
use App\Entity\WorkorderPart;
use App\Entity\OrderPart;
use App\Repository\PartRepository;
use App\Repository\OrderRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/cart")
 */

class CartController extends AbstractController
{
    private $_partRepository;
    private $_workorderRepository;
    private $_orderRepository;
    private $_manager;
    private $_session;



    public function __construct(
        PartRepository $partRepository,
        WorkorderRepository $workorderRepository,
        OrderRepository $orderRepository,
        RequestStack $requestStack,
        EntityManagerInterface $manager
    ) {
        $this->_partRepository = $partRepository;
        $this->_workorderRepository = $workorderRepository;
        $this->_orderRepository = $orderRepository;
        $this->_session = $requestStack->getSession();
        $this->_manager = $manager;
    }


    /**
     * Appel de la liste des pièces à ajouter au panier
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/add_part/{documentId?}/{mode?}', name: 'add_part', methods: ["GET"])]
    public function addPart(?int $documentId, ?string $mode): Response
    {
        $panier = $this->_session->get('panier', []);
        $panierWithData = [];

        foreach ($panier as $id => $quantity) {
            $panierWithData[] =
                [
                    'part' => $this->_partRepository->find($id),
                    'quantity' => $quantity,
                ];
        }

        return $this->redirectToRoute(
            'part_index',
            [
                'documentId' => $documentId,
                'mode' => $mode,
            ]
        );
    }

    /**
     * Liste des pièces dans le panier
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/{documentId}', name: 'cart_index')]
    public function index($workorderId): Response
    {
        $panier = $this->_session->get('panier', []);

        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'part' => $this->_partRepository->find($id),
                'quantity' => $quantity,
            ];
        }

        return $this->render(
            'cart/index.html.twig',
            [
                'items' => $panierWithData,
                'workorderId' => $workorderId,
            ]
        );
    }

    /**
     * Ajoute une pièce dans le panier des BT
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/add/workOrder/{id}/{mode}/{documentId?}', name: 'cart_add_workorder')]
    public function addWorkorderPart(
        Part $part,
        string $mode,
        ?int $documentId = null
    ): RedirectResponse {
        $panier = $this->_session->get('panier', []);

        $id = $part->getId();

        // Quantité de pièces dans le stock.
        $qteStock = $part->getStock()->getQteStock();

        // Quantité dans le panier.
        $qteCart = 0;
        if (!empty($panier)) {
            foreach ($panier as $key => $value) {
                if ($key === $id) {
                    $qteCart = $value;
                }
            }
        }

        // Test si selon la quantité disponible.
        // il est possible de mettre la pièce dans le panier.
        if (($qteStock > 0 && $qteStock > $qteCart)) {
            if (!empty($panier[$id])) {
                $panier[$id]++;
            } else {
                $panier[$id] = 1;
            }

            $this->_session->set('panier', $panier);
        }
        return $this->redirectToRoute(
            'part_index',
            [
                'mode' => $mode,
                'documentId' => $documentId,
            ]
        );
    }

    /**
     * Ajoute une pièce dans le panier
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/add/deliveryNote/{id}/{mode}/{documentId?}',
        name: 'cart_add_delivery_note'
    )]
    public function addDeliveryWorkPart(
        int $id,
        string $mode,
        ?int $documentId
    ): RedirectResponse {
        $panier = $this->_session->get('panier', []);

        if (empty($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }

        $this->_session->set('panier', $panier);

        return $this->redirectToRoute(
            'part_index',
            [
                'mode' => $mode,
                'documentId' => $documentId,
            ]
        );
    }

    /**
     * Enlève une pièce du panier
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/remove/{id}/{mode}/{documentId?}',
        name: 'cart_remove'
    )]
    public function remove(int $id, string $mode, ?int $documentId): RedirectResponse
    {
        $panier = $this->_session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $this->_session->set('panier', $panier);

        return $this->redirectToRoute(
            'part_index',
            [
                'documentId' => $documentId,
                'mode' => $mode,
            ]
        );
    }

    /**
     * Vidange du panier
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/empty/{mode}/{documentId?}',
        name: 'cart_empty'
    )]
    public function empty(string $mode, ?int $documentId): RedirectResponse
    {
        $this->_session->remove('panier');

        return $this->redirectToRoute(
            'part_index',
            [
                'documentId' => $documentId,
                'mode' => $mode,
            ]
        );
    }

    /**
     * Validation du panier de pièces pour BT et BL et commandes
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/validation/{mode}/{documentId?}',
        name: 'cart_valid'
    )]
    public function valid(string $mode, ?int $documentId): RedirectResponse
    {
        $panier = $this->_session->get('panier', []);

        // Affection des pièces du panier au BT.
        if ($mode == "workorderAddPart") {
            $this->_workorderAddPart($documentId, $panier);
        }


        if ($mode == "newDeliveryNote") {
            return $this->redirectToRoute('delivery_note_new');
        }

        if ($mode == "editDeliveryNote") {
            return $this->redirectToRoute(
                'delivery_note_edit',
                ['id' => $documentId]
            );
        }

        if ($mode == "orderAddPart") {
            $this->_orderAddPart($documentId);

            return $this->redirectToRoute(
                'order_show',
                [
                'id' => $documentId,
            ]
            );
        }

        // Effacement du panier.
        $this->_session->remove('panier');

        if ($mode == "workorderAddPart") {
            return $this->redirectToRoute(
                'work_order_show',
                [
                    'id' => $documentId,
                    'mode' => $mode,
                ]
            );
        }

        return $this->redirectToRoute(
            'delivery_note_show',
            [
                'id' => $documentId,
                'mode' => $mode,
            ]
        );
    }

    private function _workorderAddPart($id)
    {
        // Récupération du panier dans la session
        $panier = $this->_session->get('panier', []);
        // Récupération du BT dans la bdd.
        $workorder = $this->_workorderRepository->findOneBy(
            ['id' => $id]
        );
        // Puis des éventuelles pièces de ce BT.
        $workorderParts = $workorder->getWorkorderParts();

        foreach ($panier as $id => $qte) {
            $part = $this->_partRepository->find($id);
            // Vérification si la pièce est déjà dans le BT.
            foreach ($workorderParts->toArray() as $workorderPart) {
                if ($workorderPart->getPart()->getId() === $id) {

                    // Modification de la quantité sur le BT.
                    $workorderPart->setQuantity(
                        $workorderPart->getQuantity() + $qte
                    );

                    // Modification de la quantité en stock.
                    $this->_decreaseStock($part, $qte);

                    // Modification de la valeur de pièces sur le BT.
                    $workorder->setPartsPrice(
                        $workorder->getPartsPrice()
                            +
                            ($part->getSteadyPrice() * $qte)
                    );

                    // Ecriture en bdd.
                    $this->_manager->persist($workorderPart);
                    $this->_manager->persist($part);

                    // Effacement de la pièce du panier.
                    unset($panier[$id]);
                }
            }
        }

        // Traitement des pièces qui ne sont pas encore dans le BT.
        foreach ($panier as $id => $qte) {
            // Ajout de la pièce au bt
            $this->_addPartToWorkorder($id, $qte, $workorder);
            $part = $this->_partRepository->find($id);

            // Ajout de la pièce à la machine si elle n'y existe pas encore.
            $machines = $workorder->getMachines();
            $parts = $machines->first()->getParts();
            if (!$parts->contains($part)) {
                $machines->first()->addPart($part);
            }
        }

        $this->_manager->persist($workorder);
    }

    // Ajout pièce à une commande
    private function _orderAddPart($id)
    {
        // Récupération du panier dans la session
        $panier = $this->_session->get('panier', []);
    
        // Récupération de la commande dans la bdd.
        $order = $this->_orderRepository->findOneBy(
            ['id' => $id]
        );
        // Puis des éventuelles pièces de cette commande.
        $orderParts = $order->getOrderParts();

        // Pour chaque pièce dans le panier
        foreach ($panier as $id => $qte) {
            // Vérification si la pièce est déjà dans la commande
            foreach ($orderParts as $part) {
                $idPart =$part->getPart()->getId();
                if ($idPart === $id) {
                    // Modification de la quantité sur le BT.
                    $part->setQte(
                        $part->getQte() + $qte
                    );

                    // Ecriture en bdd.
                    $this->_manager->persist($part);

                    // Effacement de la pièce du panier.
                    unset($panier[$id]);
                }
            }
        }
        // Traitement des pièces qui ne sont pas encore dans le BT.
        foreach ($panier as $id => $qte) {
            // Ajout de la pièce à la commande
            $part = $this->_partRepository->find($id);
            $orderPart = new OrderPart();
            $orderPart->setOrder($order);
            $orderPart->setPart($part);
            $orderPart->setQte($qte);
            $order->addOrderPart($orderPart);
            $this->_manager->persist($orderPart);

        }
        $this->_session->remove('panier');
        $this->_manager->flush();
    }

    private function _addPartToWorkorder($id, $qte, $workorder)
    {
        $workorderPart = new WorkorderPart();
        $part = $this->_partRepository->find($id);
        $partPrice = $part->getSteadyPrice();

        $workorderPart->setPart($part);
        $workorderPart->setPrice($partPrice);
        $workorderPart->setQuantity($qte);

        $workorder->addWorkorderPart($workorderPart);

        // Ajout du prix de la pièces au BT.
        $totalPartsPrice = $partPrice * $qte;
        $workorder->setPartsPrice($workorder->getPartsPrice() + $totalPartsPrice);

        // Modification de la quantité de pièces en stock.
        $this->_decreaseStock($part, $qte);

        $this->_manager->persist($workorder);
        $this->_manager->persist($workorderPart);
        $this->_manager->persist($part);
    }

    private function _decreaseStock($part, $qte)
    {
        $stock = $part->getStock();
        $stock->setQteStock($stock->getQteStock() - $qte);
    }
}
