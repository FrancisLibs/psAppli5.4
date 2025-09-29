<?php

namespace App\Controller;

use App\Entity\Part;
use App\Entity\Machine;
use App\Repository\PartRepository;
use App\Service\ExcelExportService;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/export')]
class ExportController extends AbstractController
{
    private PartRepository $partRepository;
    private OrganisationService $organisation;
    private EntityManagerInterface $em;

    public function __construct(
        OrganisationService $organisation,
        PartRepository $partRepository,
        EntityManagerInterface $em
    ) {
        $this->partRepository = $partRepository;
        $this->organisation = $organisation;
        $this->em = $em;
    }

    #[Route('/machines', name: 'export_machines', methods: ['GET'])]
    public function exportMachines(ExcelExportService $excelExportService): Response
    {
        $user = $this->getUser();

        $headers = [
            'designation' => 'Désignation',
        ];

        // Récupère toutes les machines via EntityManager
        $machines = $this->em->getRepository(Machine::class)->findAll();

        $file = $excelExportService->exportToExcel($machines, $headers, $user);

        return $this->file(
            $file,
            'Export_Machines.xlsx',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    #[Route('/parts', name: 'export_parts', methods: ['GET'])]
    public function exportParts(ExcelExportService $excelExportService): Response
    {
        $user = $this->getUser();
        $organisationId = $this->organisation->getOrganisation()->getId();

        $parts = $this->partRepository->findPartsByOrganisation($organisationId);

        // Prépare les données à passer au service Excel
        $partsWithStock = array_map(
            fn($part) => [
                'code' => $part->getCode(),
                'designation' => $part->getDesignation(),
                'reference' => $part->getReference(),
                'remark' => $part->getRemark(),
                'active' => $part->isActive() ? 'Oui' : 'Non',
                'steadyPrice' => $part->getSteadyPrice(),
                'place' => $part->getStock()->getPlace(),
                'qteMin' => $part->getStock()->getQteMin(),
                'qteMax' => $part->getStock()->getQteMax(),
                'qteStock' => $part->getStock()->getQteStock(),
                'approQte' => $part->getStock()->getApproQte(),
            ],
            $parts
        );

        $headers = [
            'code' => 'Code',
            'designation' => 'Désignation',
            'reference' => 'Référence',
            'remark' => 'Remarque',
            'active' => 'Actif',
            'steadyPrice' => 'Prix moyen',
            'place' => 'Emplacement',
            'qteMin' => 'Quantité mini',
            'qteMax' => 'Quantité maxi',
            'qteStock' => 'Quantité en stock',
            'approQte' => 'Quantité en commande',
        ];

        $file = $excelExportService->exportToExcel($partsWithStock, $headers, $user);

        return $this->file(
            $file,
            'Export_Pieces.xlsx',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
