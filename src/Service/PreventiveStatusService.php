<?php

namespace App\Service;

use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;


class PreventiveStatusService
{
    private $workorderStatusRepository;
    private $workorderRepository;
    private $templateRepository;
    private $manager;


    public function __construct(
        WorkorderRepository $workorderRepository,
        WorkorderStatusRepository $workorderStatusRepository,
        TemplateRepository $templateRepository,
        EntityManagerInterface $manager,
    ) {
        $this->workorderRepository = $workorderRepository;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->templateRepository = $templateRepository;
        $this->manager = $manager;

    }


    /**
     *  Pour l'évolution du BT dans le temps et gérer son état : modification du statut...
     */
    public function setPreventiveStatus($organisationId)
    {
        $preventiveWorkorders = $this->workorderRepository->findAllActivePreventiveWorkorders($organisationId);
        if ($preventiveWorkorders) {
            $today = (new \Datetime())->getTimeStamp();

            foreach ($preventiveWorkorders as $workorder) {
                $preventiveTemplate = $this->templateRepository->find($workorder->getTemplateNumber());
                $preventiveDate = $preventiveTemplate->getNextDate()->getTimeStamp();
                $lateDate = $preventiveDate + ($preventiveTemplate->getDaysBeforeLate() * 24 * 3600);

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
