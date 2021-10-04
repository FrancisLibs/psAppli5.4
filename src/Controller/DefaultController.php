<?php

namespace App\Controller;

use App\Repository\WorkorderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, WorkorderRepository $workorderRepository): Response
    {
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }
        $organisation = $user->getOrganisation();
        $workorders = $workorderRepository->findByOrganisation($organisation);

        return $this->render('default/index.html.twig', [
            'workorders'    => $workorders,
        ]);
    }
}
