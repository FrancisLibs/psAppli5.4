<?php

namespace App\Controller;

use App\Entity\Connexion;
use App\Entity\Workorder;
use App\Entity\StockValue;
use App\Repository\PartRepository;
use App\Repository\UserRepository;
use App\Service\PreventiveService;
use App\Service\StockValueService;
use App\Repository\ParamsRepository;
use App\Service\UserConnexionService;
use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use App\Service\PreventiveStatusService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    private $paramsRepository;
    private $workorderRepository;
    private $templateRepository;
    private $workorderStatusRepository;
    private $userRepository;
    private $manager;
    private $partRepository;
    private $preventiveService;
    private $userConnexionService;
    private $preventiveStatusService;
    private $stockValueService;


    public function __construct(
        EntityManagerInterface $manager,
        TemplateRepository $templateRepository,
        WorkorderRepository $workorderRepository,
        ParamsRepository $paramsRepository,
        WorkorderStatusRepository $workorderStatusRepository,
        UserRepository $userRepository,
        PartRepository $partRepository,
        PreventiveService $preventiveService,
        PreventiveStatusService $preventiveStatusService,
        UserConnexionService $userConnexionService,
        StockValueService $stockValueService,
    ) {
        $this->paramsRepository = $paramsRepository;
        $this->workorderRepository = $workorderRepository;
        $this->templateRepository = $templateRepository;
        $this->manager = $manager;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->userRepository = $userRepository;
        $this->partRepository = $partRepository;
        $this->preventiveService = $preventiveService;
        $this->preventiveStatusService = $preventiveStatusService;
        $this->userConnexionService = $userConnexionService;
        $this->stockValueService = $stockValueService;
    }

    /**
     * @Route("/", name="home")
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(MailerInterface $mailer): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $organisation = $user->getOrganisation();
        $organisationId = $organisation->getId();
        $serviceId = $user->getService()->getId();
        $oneDay = (new \DateInterval('P1D')); // 'P1D' = 1jour
        $oneWeek = (new \DateInterval('P7D')); // 'P1D' = 1jour

        $this->userConnexionService->registration($user);

        // Lecture des dates de vérification cherchées dans le fichier des paramètres

        $params = $this->paramsRepository->find(1);
        $lastDate = new \DateTime();
        $lastDate->setTimestamp($params->getLastPreventiveDate()->getTimestamp())->add($oneDay);
        $today = (new \DateTime());

        // Gestion des bons de travail préventifs : Vérification à chaque connexion,
        // Rajout d'1 jour à la date enregistrée pour ne vérifier qu'une fois/jour

        // Test si traitement possible (1 fois /jour à la première connexion)
        // Si today est supérieur à lancienne date + 1 jour

        if ($today >= $lastDate || true) {
            // Traitement des préventifs à ajouter si nécessaire
            $this->preventiveService->preventiveProcessing($organisationId);

            // Surveillance des status des préventifs en cours selon leurs paramètres
            $this->preventiveStatusService->setPreventiveStatus($organisationId);

            // Changement de la date du dernier traitement
            $params->setLastPreventiveDate(new \DateTime());
            $this->manager->persist($params);
            $this->manager->flush();
        }

        // Gestion de l'enregistrement de la valeur du stock, une fois par semaine-------
        $lastStockValueDate = new \DateTime();
        $lastStockValueDate->setTimestamp($params->getLastStockValueDate()->getTimestamp());
        $lastStockValueDate->add($oneWeek);

        if ($today >= $lastStockValueDate) { // Si la date du jour est >= d'une semaine à l'ancienne date
            $this->stockValueService->computeStockValue($organisation, $organisationId, $params);
        }

        // ------------------------------------------------------------------------------
        // Récupération des utilisateurs pour l'affichage des photos
        // Par organisation ET service
        $users = $this->userRepository->findBy(
            [
                'organisation' => $organisationId,
                'service' => $serviceId,
                'active' => true,
            ],
        );
        return $this->render('default/index.html.twig', [
            'users'         => $users,
        ]);
    }
}
