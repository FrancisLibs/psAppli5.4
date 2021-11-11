<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/machineCart")
 */
class MachineCartController extends AbstractController
{
    private $machineRepository;
    private $workorderrepository;
    private $manager;
    private $requestStack;

    public function __construct(
        MachineRepository $machineRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack,
        WorkorderRepository $workorderrepository
    ) {
        $this->machineRepository = $machineRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->workorderrepository = $workorderrepository;
    }

    /**
     * 
     * @Route("/add/{id}/{mode}/{workorderId?}", name="add_machine_to_cart", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request   $request
     * @param int       $id   // L'id de la machine à ajouter
     * @param string    $mode      
     * 
     * @return redirectToRoute
     */
    public function addMachine(Request $request, int $id, $mode = null, int $workorderId = null): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        if (!in_array($id, $machines)) {
            $machines[] = $id;
        }

        $session->set('machines', $machines);

        return $this->redirectToRoute('machine_index', [
            'mode' => $mode,
            'workorderId'   => $workorderId,
        ]);
    }

    /**
     * 
     * @Route("/remove/{id?}/{workorderId?}", name="remove_machine_from_cart", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @return RedirectResponse
     */
    public function removeMachine(?int $id, ?int $workorderId, string $mode = null): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        unset($machines[array_search($id, $machines)]);

        $session->set('machines', $machines);

        if ($mode == 'newPreventive') {
            return $this->redirectToRoute('preventive_new', [
                'mode' => $mode,
            ]);
        }
        if ($mode == 'editPreventive') {
            return $this->redirectToRoute('preventive_edit', [
                'mode' => $mode,
            ]);
        }
        return $this->redirectToRoute('machine_index', [
            'mode'  =>  "editPreventive",
            'workorderId'   =>  $workorderId,
        ]);
    }
}
