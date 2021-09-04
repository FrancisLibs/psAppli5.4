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
    public function index(Request $request, WorkorderRepository $workorderRepository,PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $workorders = $workorderRepository->findAll();
        $workorders = $paginator->paginate(
            $workorders, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );

        return $this->render('default/index.html.twig', [
            'workorders'    => $workorders,
        ]);
    }
}
