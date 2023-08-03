<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Form\Stock1Type;
use App\Repository\PartRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/stock")
 */
class StockController extends AbstractController
{
    private $partRepository;
    private $manager;

    public function __construct(EntityManagerInterface $manager, PartRepository $partRepository)
    {
        $this->partRepository = $partRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/", name="stock_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(StockRepository $stockRepository): Response
    {
        return $this->render('stock/index.html.twig', [
            'stocks' => $stockRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="stock_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request): Response
    {
        $stock = new Stock();
        $form = $this->createForm(Stock1Type::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($stock);
            $this->manager->flush();

            return $this->redirectToRoute('stock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('stock/new.html.twig', [
            'stock' => $stock,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="stock_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Stock $stock): Response
    {
        return $this->render('stock/show.html.twig', [
            'stock' => $stock,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="stock_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Stock $stock): Response
    {
        $form = $this->createForm(Stock1Type::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute('stock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('stock/edit.html.twig', [
            'stock' => $stock,
            'form' => $form,
        ]);
    }
}
