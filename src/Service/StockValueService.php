<?php

namespace App\Service;

use App\Entity\StockValue;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockValueService
{
    private $manager;
    private $partRepository;

    public function __construct(EntityManagerInterface $manager, PartRepository $partRepository)
    {
        $this->manager = $manager;
        $this->partRepository = $partRepository;
    }

    // Calcul du montant du stock
    public function computeStockValue($organisation, $organisationId, $params)
    {
        $totalStock = $this->partRepository->findTotalStock($organisationId);

        // Création de l'enregistrement
        $stockValue = new StockValue();
        $stockValue->setValue($totalStock)
            ->setDate(new \Datetime())
            ->setOrganisation($organisation);
        $this->manager->persist($stockValue);

        // Calcul nouvelle date dans le fichier params
        $params->setLastStockValueDate(new \DateTime());

        $this->manager->persist($params);
        $this->manager->flush();

        return;
    }
}
