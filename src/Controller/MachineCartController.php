<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/machineCart")
 */
class MachineCartController extends AbstractController
{
    private $_requestStack;


    public function __construct(RequestStack $requestStack)
    {
        $this->_requestStack = $requestStack;
    }

    
    /**
     * @return redirectToRoute
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/add/{id}/{mode}/{documentId?}', 
        name: 'add_machine_to_cart', 
        methods:["GET"]
    )]
    public function addMachine(
        int $id, 
        string $mode = null, 
        ?int $documentId = null
    ): Response {
        $session = $this->_requestStack->getSession();
        $machines = $session->get('machines', []);
        if (!in_array($id, $machines)) {
            $machines[] = $id;
        }

        $session->set('machines', $machines);
        if ($mode == "newWorkorder") {
            return $this->redirectToRoute(
                'work_order_new', ['mode' => $mode]
            );
        }

        return $this->redirectToRoute(
            'machine_index', [
            'mode' => $mode,
            'documentId'   => $documentId,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(
        '/remove/{id}/{mode}/{documentId?}', 
        name: 'remove_machine_from_cart', 
        methods:["GET"]
    )]
    public function removeMachine(int $id, string $mode, ?int $documentId): Response
    {
        $session = $this->_requestStack->getSession();
        $machines = $session->get('machines', []);
        unset($machines[array_search($id, $machines)]);

        $session->set('machines', $machines);

        if ($mode == 'newPreventive') {
            return $this->redirectToRoute('template_new');
        }
        if ($mode == 'selectPreventive') {
            return $this->redirectToRoute(
                'machine_index', [
                'documentId' => $documentId,
                'mode' => $mode,
                ]
            );
        }
        return $this->redirectToRoute(
            'machine_index', [
            'mode'  =>  $mode,
            'templateId'   =>  $documentId,
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/machineChoice/{mode}/{documentId?}', 
        name: 'machine_choice', 
        methods:["GET"]
    )]
    #[Route(
        '/preventiveNew/{mode}/{documentId?}', 
        name: 'preventive_new', 
        methods:["GET"]
    )]
    public function machineChoice(string $mode, ?int $documentId)
    {
        $session = $this->_requestStack->getSession();
        $session->remove('machines');

        switch ($mode) {
        case "preventive":
            return $this->redirectToRoute(
                'template_new', [
                    'mode'  =>  $mode,
                    ]
            );
                break;
        case "newWorkorder":
            return $this->redirectToRoute(
                'machine_index', [
                    'mode'  =>  $mode,
                    ]
            );
                break;
        }

        return $this->redirectToRoute(
            'machine_index', [
            'mode'  =>  $mode,
            'documentId'   =>  $documentId,
            ]
        );
    }
}
