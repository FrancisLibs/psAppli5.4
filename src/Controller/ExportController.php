<?php

namespace App\Controller;

use App\Entity\Part;
use App\Entity\Machine;
use App\Repository\PartRepository;
use App\Service\ExcelExportService;
use App\Service\OrganisationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportController extends AbstractController
{
    protected $partRepository;
    protected $organisation;

    public function __construct(
        OrganisationService $organisation,
        PartRepository $partRepository,
    ) {
        $this->partRepository = $partRepository;
        $this->organisation = $organisation;
    }
    /**
     * @Route("/export/machines", name="export_machines")
     */
    public function exportMachines(ExcelExportService $excelExportService): Response
    {
        $user = $this->getUser();

        $headers = [
            'designation' => 'Désignation',
        ];

        $file = $excelExportService->exportToExcel(Machine::class, $headers, $user);

        return $this->file($file, 'Export Machines.xlsx', ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    /**
     * @Route("/export/parts", name="export_parts")
     */
    public function exportParts(ExcelExportService $excelExportService, ): Response
    {
        $user = $this->getUser();
        $organisationId = $this->organisation->getOrganisation()->getId();

        $parts = $this->partRepository->findPartsByOrganisation($organisationId);
        
        $partsWithStock = array_map(
            function ($part) {
                return [
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
                ];
            }, $parts
        );

        $headers = [
            'code' => 'Code',
            'designation' => 'Désignation',
            'reference' => 'Référence',
            'remark' => 'REmarque',
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
            $file, 'Export_Pieces.xlsx', 
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }
}
