<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/supply")
 */
class SupplyController extends AbstractController
{
    protected $manager;
    protected $partRepository;
    protected $organisation;


    public function __construct(
        EntityManagerInterface $manager,
        PartRepository $partRepository,
        OrganisationService $organisation,
    ) {
        $this->manager = $manager;
        $this->partRepository = $partRepository;
        $this->organisation = $organisation;
    }


    #[Route('/partsToBuy', name: 'parts_to_buy')]
    #[IsGranted('ROLE_USER')]
    public function partsToBuy(): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findPartsToBuy($organisation);

        return $this->render(
            'supply/partsToBuy.html.twig', 
            ['parts' => $parts]
        );
    }

    #[Route('/reception', name: 'parts_reception')]
    #[IsGranted('ROLE_USER')]
    public function partReception(): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findPartsToBuy($organisation);

        return $this->render(
            'supply/partsToBuy.html.twig', [
            'parts' => $parts,
            ]
        );
    }

    /**
     * Return the "to buy" parts about a provider
     *
     * @return Response
     */
    #[Route('/providerPart/{id}', name: 'parts_provider')]
    #[IsGranted('ROLE_USER')]
    public function providerPart(Provider $provider): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findProviderParts($organisation, $provider);

        return $this->render(
            'supply/providerParts.html.twig', [
            'provider' => $provider,
            'parts' => $parts,
            ]
        );
    }
}
