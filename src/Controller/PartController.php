<?php

namespace App\Controller;

use App\Repository\PartRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PartController extends AbstractController
{
    /**
     * @Route("/part", name="part")
     */
    public function index(PartRepository $partRepository): Response
    {
        $parts = $partRepository->findAll();
        
        return $this->render('part/index.html.twig', [
            'parts' => '$parts',
        ]);
    }
}
