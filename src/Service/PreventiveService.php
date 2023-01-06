<?php

namespace App\Service;

use App\Entity\Workorder;
use App\Entity\WorkorderStatus;
use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;

class PreventiveService
{
    private $workorderStatusRepository;
    private $templateRepository;
    private $workorderRepository;
    private $manager;

    public function __construct(
        TemplateRepository $templateRepository,
        WorkorderRepository $workorderRepository,
        EntityManagerInterface $manager,
        WorkorderStatusRepository $workorderStatusRepository,
    ) {
        $this->templateRepository = $templateRepository;
        $this->workorderRepository = $workorderRepository;
        $this->manager = $manager;
        $this->workorderStatusRepository = $workorderStatusRepository;
    }

    /**
     * Création des BT préventifs à partir des templates
     */
    public function preventiveProcessing($organisationId)
    {
        // Recherche des templates préventifs
        $templates = $this->templateRepository->findAllActiveTemplates($organisationId);


        $today = (new \DateTime())->getTimestamp();

        foreach ($templates as $template) {
            //dd($template);
            // Prochaine date en secondes
            $nextDate = $template->getNextDate()->getTimestamp(); // Date de réalisation
            // Jours avant la date en secondes
            $secondsBefore = $template->getDaysBefore() * 24 * 60 * 60; // Jours avant réalisation
            // Date finale à prende en compte
            $nextComputeDate = $nextDate - $secondsBefore; // Date finale d'activation en secondes

            // Test si template éligible : si la date du préventif est supérieure à la date du jour
            if ($today >= $nextComputeDate) {
                // Contrôle si BT préventif n'est pas déjà actif
                if (!$this->workorderRepository->countPreventiveWorkorder($template->getTemplateNumber())) {
                    // Création du BT préventif, en récupérant les infos sur le template préventif
                    $workorder = new Workorder();
                    $workorder->setCreatedAt(new \DateTime())
                        ->setPreventiveDate($template->getNextDate())
                        ->setRequest($template->getRequest())
                        ->setRemark($template->getRemark())
                        ->setOrganisation($template->getOrganisation())
                        ->setTemplateNumber($template->getTemplateNumber())
                        ->setUser($template->getUser())
                        ->setType(Workorder::PREVENTIF)
                        ->setPreventive(true)
                        ->setDaysBeforeLate($template->getDuration())
                        ->setCalendarTitle($template->getCalendarTitle());

                    $status = $this->workorderStatusRepository->findOneBy(['name' => 'EN_PREP.']);
                    $workorder->setWorkorderStatus($status);

                    $machines = $template->getMachines();
                    foreach ($machines as $machine) {
                        $workorder->addMachine($machine);
                    }
                    $this->manager->persist($workorder);
                }

                $this->manager->flush();
            }
        }
        return;
    }
}
