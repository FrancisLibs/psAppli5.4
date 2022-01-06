<?php

namespace App\Controller;

use App\Repository\MachineRepository;
use App\Repository\TemplateRepository;
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
    private $templateRepository;
    private $manager;
    private $requestStack;

    public function __construct(
        MachineRepository $machineRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack,
        TemplateRepository $templateRepository
    ) {
        $this->machineRepository = $machineRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->TemplateRepository = $templateRepository;
    }

    /**
     * 
     * @Route("/add/{id}/{mode}/{documentId?}", name="add_machine_to_cart", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request   $request
     * @param int       $id   // L'id de la machine à ajouter
     * @param string    $mode      
     * 
     * @return redirectToRoute
     */
    public function addMachine(Request $request, int $id, $mode = null, ?int $documentId): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        if (!in_array($id, $machines)) {
            $machines[] = $id;
        }

        $session->set('machines', $machines);
        
        return $this->redirectToRoute('machine_index', [
            'mode' => $mode,
            'documentId'   => $documentId,
        ]);
    }

    /**
     * 
     * @Route("/remove/{id}/{mode}/{documentId?}", name="remove_machine_from_cart", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @return RedirectResponse
     */
    public function removeMachine(int $id, string $mode, ?int $documentId): Response
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        unset($machines[array_search($id, $machines)]);

        $session->set('machines', $machines);

        if ($mode == 'newPreventive') {
            return $this->redirectToRoute('template_new');
        }
        if ($mode == 'selectPreventive') {
            return $this->redirectToRoute('machine_index', [
                'documentId' => $documentId,
                'mode' => $mode,
            ]);
        }
        return $this->redirectToRoute('machine_index', [
            'mode'  =>  $mode,
            'templateId'   =>  $documentId,
        ]);
    }
}
