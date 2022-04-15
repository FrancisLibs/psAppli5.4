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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @param   string      $mode
     * @return  Response
     */
    public function addPart(?int $documentId, ?string $mode): Response
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
     * Ajoute une pièce dans le panier des BT
     *  
     * @Route("/add/workOrder/{id}/{mode}/{documentId?}", name="cart_add_workorder")
     * @Security("is_granted('ROLE_USER')")
     * @param   id              $id de la pièce ajoutée
     * @param   workorderId     id du workorder
     * @param   mode
     * @return redirectResponse
     */
    public function addWorkorderPart(Part $part, string $mode, ?int $documentId = null): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        $id = $part->getId();

        // Quantité de pièces dans le stock
        $qteStock = $part->getStock()->getQteStock();

        // Quantité dans le panier
        $qteCart = 0;
        if (!empty($panier)) {
            foreach ($panier as $key => $value) {
                if ($key == $id) {
                    $qteCart = $value;
                }
            }
        }

        // Test si selon la quantité disponible, il est possible de mettre la pièce dans le panier
        if (($qteStock > 0 && $qteStock > $qteCart)) {
            if (!empty($panier[$id])) {
                $panier[$id]++;
            } else {
                $panier[$id] = 1;
            }

            $session->set('panier', $panier);
        }
        return $this->redirectToRoute('part_index', [
            'mode' => $mode,
            'documentId' => $documentId,
        ]);
    }

    /**
     * Ajoute une pièce dans le panier des BL
     * 
     * @Route("/add/deliveryNote/{id}/{mode}/{documentId?}", name="cart_add_delivery_note")
     * @Security("is_granted('ROLE_USER')")
     * @param   id              $id de la pièce ajoutée
     * @param   documentId     id du workorder
     * @param   mode
     * @return redirectResponse
     */
    public function addDeliveryWorkPart(int $id, string $mode, ?int $documentId): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (empty($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('part_index', [
            'mode' => $mode,
            'documentId' => $documentId,
        ]);
    }

    /**
     * Enlève une pièce du panier
     * 
     * @Route("/remove/{id}/{mode}/{documentId?}", name="cart_remove")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param   id              id de la pièce à enlever
     * @param   workorderId     id du workorder
     * @return  RedirectResponse
     */
    public function remove(int $id, string $mode, ?int $documentId): RedirectResponse
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
     * @Route("/empty/{mode}/{documentId?}", name="cart_empty")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param int documentId   id du document actuel
     * @param string mode
     * @return RedirectResponse
     */
    public function empty(string $mode, ?int $documentId): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $session->remove('panier');

        return $this->redirectToRoute('part_index', [
            'documentId' => $documentId,
            'mode' => $mode,
        ]);
    }

    /**
     * Validation du panier
     * 
     * @Route("/validation/{mode}/{documentId?}", name="cart_valid")
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param documentId   id du document
     * 
     * @return RedirectResponse
     */
    public function valid(string $mode, ?int $documentId): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        // Affection des pièces du panier au BT
        // Si le BT a déjà des pièces
        if ($mode == "workorderAddPart") {
            // Récupération du BT dans la bdd
            $workorder = $this->workorderRepository->findOneBy(['id' => $documentId]);
            // Puis des pièces de ce BT
            $workorderParts = $workorder->getWorkorderParts();
           
            foreach ($panier as $id => $qte) {
                $part = $this->partRepository->find($id);

                foreach ($workorderParts->toArray() as $workorderPart) {

                    if ($workorderPart->getPart()->getId() === $id) {

                        // Modification de la quantité sur le BT
                        $workorderPart->setQuantity($workorderPart->getQuantity() + $qte);

                        // Modification de la quantité en stock
                        $this->decreaseStock($part, $qte);

                        // Modification de la valeur de pièces sur le BT
                        $workorder->setPartsPrice($workorder->getPartsPrice() + $part->getSteadyPrice() * $qte);

                        // Ecriture en bdd
                        $this->manager->persist($workorderPart);
                        $this->manager->persist($part);

                        // Effacement de la pièce du panier
                        unset($panier[$id]);
                    }
                }
            }
            // Traitement des pièces qui ne sont pas encore dans le BT
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

            $this->manager->persist($workorder);
        }

        if ($mode == "newDeliveryNote") {
            return $this->redirectToRoute('delivery_note_new');
        }

        if ($mode == "editDeliveryNote") {
            return $this->redirectToRoute('delivery_note_edit', [
                'id' => $documentId,
            ]);
        }

        $this->manager->flush();

        // Effacement du panier
        $session->remove('panier');

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

        // Ajout du prix de la pièces au BT
        $partPrice = $part->getSteadyPrice();
        $totalPartsPrice = $partPrice * $qte;
        $workorder->setPartsPrice($workorder->getPartsPrice() + $totalPartsPrice);

        // Modification de la quantité de pièces en stock
        $this->decreaseStock($part, $qte);

        $this->manager->persist($workorder);
        $this->manager->persist($workorderPart);
        $this->manager->persist($part);
    }

    private function decreaseStock($part, $qte)
    {
        $stock = $part->getStock();
        $stock->setQteStock($stock->getQteStock() - $qte);
    }
}
