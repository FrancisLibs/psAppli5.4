<?php

namespace App\Controller;

use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/supply")
 */
class SupplyController extends AbstractController
{
    private $entityManager;
    private $partRepository;

    public function __construct(
        EntityManagerInterface $manager,
        PartRepository $partRepository
    ) {
        $this->entityManager = $manager;
        $this->partRepository = $partRepository;
    }

    /**
     * @Route("/partsToBuy", name="parts_to_buy")
     */
    public function partsToBuy(): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $parts = $this->partRepository->findPartsToBuy($organisation);

        return $this->render('supply/partsToBuy.html.twig', [
            'parts' => $parts,
        ]);
    }

    /**
     * @Route("/reception", name="parts_reception")
     */
    public function partReception(): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $parts = $this->partRepository->findPartsToBuy($organisation);

        return $this->render('supply/partsToBuy.html.twig', [
            'parts' => $parts,
        ]);
    }
}
