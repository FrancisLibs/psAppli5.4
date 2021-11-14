<?php

namespace App\Controller;

use App\Repository\ParamsRepository;
use App\Repository\WorkorderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    private $paramsRepository;

    public function __construct(ParamsRepository $paramsRepository){
        $this->paramsRepository = $paramsRepository;
    }

    /**
     * @Route("/", name="home")
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(WorkorderRepository $workorderRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $organisation = $user->getOrganisation()->getid();
        $workorders = $workorderRepository->findAllWorkorders($organisation);

        // Gestion des bons de travail préventifs
        // Date du jour
        $today = new \DateTime('now');
        // Dernière date 
        $lastDate = $this->paramsRepository->find(1)->getLastPreventiveDate();
        //dd($lastDate);



        return $this->render('default/index.html.twig', [
            'workorders'    => $workorders,
        ]);
    }
}
