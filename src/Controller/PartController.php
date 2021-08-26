<?php

namespace App\Controller;

use App\Repository\PartRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PartController extends AbstractController
{
    /**
     * @Route("/part", name="part")
     */
    public function index(Request $request, PartRepository $partRepository, PaginatorInterface $paginator): Response
    {
        $parts = $partRepository->findAll();
        $parts = $paginator->paginate(
            $parts, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );
        
        return $this->render('part/index.html.twig', [
            'parts' => $parts,
        ]);
    }
}
