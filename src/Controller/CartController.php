<?php

namespace App\Controller;

use App\Entity\Part;
use App\Data\SearchPart;
use App\Entity\Workorder;
use App\Entity\WorkorderPart;
use App\Entity\DeliveryNotePart;
use App\Repository\PartRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    private $workorderRepository;
    private $deliveryNoteRepository;
    private $requestStack;
    private $manager;

    public function __construct(
        PartRepository $partRepository,
        WorkorderRepository $workorderRepository,
        DeliveryNoteRepository $deliveryNoteRepository,
        RequestStack $requestStack,
        EntityManagerInterface $manager
    ) {
        $this->deliveryNoteRepository = $deliveryNoteRepository;
        $this->partRepository = $partRepository;
        $this->workorderRepository = $workorderRepository;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Appel de la liste des pièces à ajouter au panier
     * 
     * @Route("/add_part/{documentId?}/{mode?}", name="add_part", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * @param   Workorder   $workorder
     * @param   Request     $request 
     * @param   string      $mode
     * @return  Response
     */
    public function addPart(Request $request, ?int $documentId, ?string $mode): Response
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

        return $this->redirectToRoute('part_index', [
            'documentId' => $documentId,
            'mode' => $mode,
        ]);
    }

    /**
     * Liste des pièces dans le panier
     * 
     * @Route("/{documentId}", name="cart_index")
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
     * @Route("/add/{id}/{mode}/{documentId?}", name="cart_add")
     * @Security("is_granted('ROLE_USER')")
     * @param   id              $id de la pièce ajoutée
     * @param   workorderId     id du workorder
     * @param   mode
     * @return redirectResponse
     */
    public function add(Part $part, string $mode, ?int $documentId = null): RedirectResponse
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
        if (($qteStock > 0 && $qteStock > $qteCart) || $mode = "receivedPart") {

            if (!empty($panier[$id])) {
                $panier[$id]++;
            } else {
                $panier[$id] = 1;
            }

            $session->set('panier', $panier);
        }
        return $this->redirectToRoute('part_index', [
            'documentId' => $documentId,
            'mode' => $mode,
        ]);
    }

    /**
     * Enlève une pièce du panier
     * @Route("/remove/{id}/{documentId}/{mode}", name="cart_remove")
     * @Security("is_granted('ROLE_USER')")
     * @param   id              id de la pièce à enlever
     * @param   workorderId     id du workorder
     * @return  RedirectResponse
     */
    public function remove(int $id, int $documentId, string $mode): Response
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('part_index', [
            'documentId' => $documentId,
            'mode' => $mode,
        ]);
    }

    /**
     * Vidange du panier
     * 
     * @Route("/empty/{documentId}/{mode}", name="cart_empty")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param int documentId   id du document actuel
     * @param string mode
     * @return RedirectResponse
     */
    public function empty(int $documentId, string $mode): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);
        foreach ($panier as $id => $qte) {
            unset($panier[$id]);
        }
        $session->set('panier', $panier);

        return $this->redirectToRoute('part_index', [
            'documentId' => $documentId,
            'mode' => $mode,
        ]);
    }

    /**
     * Validation du panier
     * 
     * @Route("/validation/{documentId}/{mode}", name="cart_valid")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param workorderId   id du panier
     * 
     * @return RedirectResponse
     */
    public function valid(int $documentId, string $mode): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);


        // Affection des pièces du panier au BT ou au BL

        if ($mode == "workorderAddPart") {
            $workorder = $this->workorderRepository->findOneBy(['id' => $documentId]);
            $workorderParts = $workorder->getWorkorderParts();
            if (!$workorderParts->isEmpty()) {
                foreach ($panier as $id => $qte) {
                    // Vérification si la pièce n'est pas déjà dans le BT
                    foreach ($workorderParts->toArray() as $workorderPart) {
                        if ($workorderPart->getPart()->getId() == $id) {

                            // Modification de la quantité sur le BT
                            $workorderPart->setQuantity($workorderPart->getQuantity() + $qte);

                            // Modification de la quantité en stock
                            $part = $this->partRepository->find($id);
                            $this->decreaseStock($part, $qte);

                            // Ecriture en bdd
                            $this->manager->persist($workorderPart);
                            $this->manager->persist($part);

                            // Effacement de la pièce du panier
                            unset($panier[$id]);
                        }
                    }
                }
                foreach ($panier as $id => $qte) {
                    
                    // Ajout de la pièce au bt
                    $this->addPartToWorkorder($id, $qte, $workorder);
                    $part = $this->partRepository->find($id);

                    // Ajout de la pièce à la machine si elle n'y existe pas encore
                    $machines = $workorder->getMachines();
                    $parts = $machines->first()->getParts();
                    if (!$parts->contains($part)) {
                        $machines->first()->addPart($part);
                    }
                }
            } else {
                foreach ($panier as $id => $qte) {
                    $this->addPartToWorkorder($id, $qte, $workorder);
                }
            }
            $this->manager->persist($workorder);
        }

        if ($mode == "receivedPart") {
            $deliveryNote = $this->deliveryNoteRepository->findOneBy(['id' => $documentId]);

            foreach ($panier as $id => $qte) {
                // Ajout de la pièce au bt
                $deliveryNotePart = new DeliveryNotePart();
                $part = $this->partRepository->find($id);

                $deliveryNotePart->setPart($part);
                $deliveryNotePart->setQuantity($qte);

                $deliveryNote->addDeliveryNotePart($deliveryNotePart);

                // Rectification de la quantité de pièce en stock
                $this->decreaseStock($part, $qte);
                
                $this->manager->persist($deliveryNotePart);
            }
            $this->manager->persist($deliveryNote);
        }

        $this->manager->flush();

        // Effacement du panier
        foreach ($panier as $id => $qte) {
            unset($panier[$id]);
        }

        //Sauvegarde du panier 
        $session->set('panier', $panier);

        if ($mode == "workorderAddPart") {
            return $this->redirectToRoute('work_order_show', [
                'id' => $documentId,
                'mode' => $mode,
            ]);
        }

        return $this->redirectToRoute('delivery_note_show', [
            'id' => $documentId,
            'mode' => $mode,
        ]);
    }

    private function addPartToWorkorder($id, $qte, $workorder)
    {
        $workorderPart = new WorkorderPart();
        $part = $this->partRepository->find($id);
        $workorderPart->setPart($part);
        $workorderPart->setQuantity($qte);
        $workorder->addWorkorderPart($workorderPart);

        // Modification de la quantité de pièces en stock
        $this->decreaseStock($part, $qte);

        $this->manager->persist($workorderPart);
        $this->manager->persist($part);
    }

    private function decreaseStock($part, $qte)
    {
        $stock = $part->getStock();
        $stock->setQteStock($stock->getQteStock() - $qte);
    }
}
