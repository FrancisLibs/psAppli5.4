<?php

namespace App\Service;

use App\Entity\Workorder;
use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;

class PreventiveService
{
    private $_workorderStatusRepository;
    private $_templateRepository;
    private $_workorderRepository;
    private $_manager;


    public function __construct(
        TemplateRepository $templateRepository,
        WorkorderRepository $workorderRepository,
        EntityManagerInterface $manager,
        WorkorderStatusRepository $workorderStatusRepository,
    ) {
        $this->_templateRepository = $templateRepository;
        $this->_workorderRepository = $workorderRepository;
        $this->_manager = $manager;
        $this->_workorderStatusRepository = $workorderStatusRepository;
    }


    /**
     * Création des BT préventifs à partir des templates
     */
    public function preventiveProcessing($organisationId)
    {
        // Recherche des templates préventifs
        $templates = $this->_templateRepository->findAllActiveTemplates($organisationId);

        $today = (new \DateTime())->getTimestamp();

        foreach ($templates as $template) {
            // Prochaine date en secondes
            $nextDate = $template->getNextDate()->getTimestamp();

            // Jours avant la date
            $secondsBefore = ($template->getDaysBefore() * 24 * 3600);

            // Date finale à prende en compte
            // Date finale d'activation en secondes
            $nextComputeDate = $nextDate - $secondsBefore; 

            // Test si template éligible : 
            // si la date du préventif est supérieure à la date du jour
            if ($today >= $nextComputeDate) {
                // Contrôle si BT préventif n'est pas déjà actif
                if (!$this->_workorderRepository->countPreventiveActiveWorkorder(
                    $template->getTemplateNumber()
                )
                ) {
                    // Création du BT préventif, 
                    // en récupérant les infos sur le template préventif
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

                    $status = $this->_workorderStatusRepository->findOneBy(
                        ['name' => 'EN_PREP.']
                    );
                    $workorder->setWorkorderStatus($status);

                    $machines = $template->getMachines();
                    foreach ($machines as $machine) {
                        $workorder->addMachine($machine);
                    }
                    // Ecriture en Bdd pour prendre en compte
                    // tous les BT à la prochaine occurence
                    $this->_manager->persist($workorder);
                    $this->_manager->flush();
                }
            }
        }
        return;
    }
}
