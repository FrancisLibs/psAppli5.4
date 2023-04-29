<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GlobalSearchController extends AbstractController
{
    #[Route('/global/search', name: 'app_global_search')]
    public function index(): Response
    {
        return $this->render('global_search/index.html.twig', [
            'controller_name' => 'GlobalSearchController',
        ]);
    }
}
