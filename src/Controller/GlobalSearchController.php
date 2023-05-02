<?php

namespace App\Controller;

use App\Data\SearchGlobal;
use App\Form\GlobalSearchType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class GlobalSearchController extends AbstractController
{
    #[Route('/global/search', name: 'app_global_search')]
    public function index(Request $request)
    {
        $data = new SearchGlobal();

        $form = $this->createForm(GlobalSearchType::class, $data);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            dd($form->getData());
        }

        return $this->render('global_search/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
