<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
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
    private $organisation;

    public function __construct(
        EntityManagerInterface $manager,
        PartRepository $partRepository,
        OrganisationService $organisation,
    ) {
        $this->entityManager = $manager;
        $this->partRepository = $partRepository;
        $this->organisation = $organisation;
    }

    /**
     * @Route("/partsToBuy", name="parts_to_buy")
     */
    public function partsToBuy(): Response
    {
        $organisation = $this->organisation->getOrganisation();
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
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findPartsToBuy($organisation);

        return $this->render('supply/partsToBuy.html.twig', [
            'parts' => $parts,
        ]);
    }

    /**
     * Return the "to buy" parts about a provider
     * 
     *@Route("/providerPart/{id}", name="parts_provider")
     *
     * @return Response
     */
    public function providerPart(Provider $provider): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findProviderParts($organisation, $provider);

        return $this->render('supply/providerParts.html.twig', [
            'provider' => $provider,
            'parts' => $parts,
        ]);
    }
}
