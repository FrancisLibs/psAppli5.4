<?php

namespace App\Service;

use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;


class PreventiveStatusService
{
    private $workorderStatusRepository;
    private $workorderRepository;
    private $manager;

    public function __construct(
        WorkorderRepository $workorderRepository,
        WorkorderStatusRepository $workorderStatusRepository,
        EntityManagerInterface $manager,
    ) {
        $this->workorderRepository = $workorderRepository;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->manager = $manager;
    }

    /**
     *  Pour l'évolution du BT dans le temps et gérer son état : modification du statut...
     */
    public function setPreventiveStatus($organisationId)
    {
        // Jour actuel
        $today = new \DateTime();

        $preventiveWorkorders = $this->workorderRepository->findAllActivePreventiveWorkorders($organisationId);

        if ($preventiveWorkorders) {

            $today = (new \Datetime())->getTimeStamp();

            foreach ($preventiveWorkorders as $workorder) {

                $preventiveDate = $workorder->getPreventiveDate()->getTimeStamp();
                $lateDate = $preventiveDate + ($workorder->getDaysBeforeLate() * 24 * 60 * 60);

                if ($today > $preventiveDate) {
                    $status = $this->workorderStatusRepository->findOneByName('EN_COURS');
                    $workorder->setWorkorderStatus($status);
                }

                if ($today > $lateDate) {
                    $status = $this->workorderStatusRepository->findOneByName('EN_RETARD');
                    $workorder->setWorkorderStatus($status);
                }

                $this->manager->persist($workorder);
            }
            $this->manager->flush();
        }
        return;
    }
}
