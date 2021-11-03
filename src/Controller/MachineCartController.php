<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MachineCartController extends AbstractController
{
    private $machineRepository;
    private $manager;
    private $requestStack;

    public function __construct(MachineRepository $machineRepository, EntityManagerInterface $manager, RequestStack $requestStack)
    {
        $this->machineRepository = $machineRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
    }

    /**
     * 
     * @Route("/add/preventive/{id}", name="add_machine_to_cart", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function addMachine(Request $request, int $id): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        if (!in_array($id, $machines)) {
            $machines[] = $id;
        }

        $session->set('machines', $machines);
        $mode = 'selectPreventive';

        return $this->redirectToRoute('machine_index', [
            'mode' => $mode,
        ]);
    }

    /**
     * 
     * @Route("/remove/preventive/{id}/{new?}", name="remove_machine_from_cart", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function removeMachine(Request $request, int $id, bool $new = false): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        unset($machines[array_search($id, $machines)]);

        $session->set('machines', $machines);
        $mode = 'selectPreventive';
        if ($new) {
            return $this->redirectToRoute('preventive_new', [
                'mode' => $mode,
            ]);
        }
        return $this->redirectToRoute('machine_index', [
            'mode' => $mode,
        ]);
    }
}
