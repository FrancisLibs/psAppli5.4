<?php

namespace App\Controller;

use App\Repository\PartRepository;
use App\Repository\UserRepository;
use App\Service\PreventiveService;
use App\Service\StockValueService;
use App\Repository\ParamsRepository;
use App\Service\OrganisationService;
use App\Service\UserConnexionService;
use App\Repository\WorkorderRepository;
use App\Service\PreventiveStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    protected $paramsRepository;
    protected $userRepository;
    protected $manager;
    protected $preventiveService;
    protected $userConnexionService;
    protected $preventiveStatusService;
    protected $stockValueService;
    protected $organisation;
    protected $workorderRepository;
    protected $partRepository;


    public function __construct(
        EntityManagerInterface $manager,
        ParamsRepository $paramsRepository,
        UserRepository $userRepository,
        PreventiveService $preventiveService,
        PreventiveStatusService $preventiveStatusService,
        UserConnexionService $userConnexionService,
        StockValueService $stockValueService,
        OrganisationService $organisation,
        WorkorderRepository $workorderRepository,
        PartRepository $partRepository,
    ) {
        $this->paramsRepository = $paramsRepository;
        $this->manager = $manager;
        $this->userRepository = $userRepository;
        $this->preventiveService = $preventiveService;
        $this->preventiveStatusService = $preventiveStatusService;
        $this->userConnexionService = $userConnexionService;
        $this->stockValueService = $stockValueService;
        $this->organisation = $organisation;
        $this->workorderRepository = $workorderRepository;
        $this->partRepository = $partRepository;
    }


    #[Route('/', name: 'home')]
    #[IsGranted('ROLE_USER')]
    public function index(MailerInterface $mailer): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $organisation = $this->organisation->getOrganisation();
        $organisationId = $organisation->getId();
        $serviceId = $this->organisation->getService()->getId();
        $oneDay = (new \DateInterval('P1D')); // 'P1D' = 1jour.
        $oneWeek = (new \DateInterval('P7D')); // 'P7D' = 7jour.

        $this->userConnexionService->registration($user);

        // Lecture des dates de vérification cherchées dans 
        // le fichier des paramètres.

        $params = $this->paramsRepository->find(1);
        $lastDate = new \DateTime();
        $lastDate->setTimestamp(
            $params->getLastPreventiveDate()->getTimestamp()
        )->add($oneDay);
        $today = (new \DateTime());

        // Gestion des bons de travail préventifs : Vérification à chaque connexion.
        // Rajout d'1 jour à la date enregistrée pour ne vérifier qu'une fois/jour.
        // Test si traitement possible (1 fois /jour à la première connexion).
        // Si today est supérieur à lancienne date + 1 jour.

        if ($today >= $lastDate) {
            // Traitement des préventifs à ajouter si nécessaire.
            $this->preventiveService->preventiveProcessing($organisationId);

            // Surveillance des status des préventifs en 
            // cours selon leurs paramètres.
            $this->preventiveStatusService->setPreventiveStatus($organisationId);

            // Changement de la date du dernier traitement.
            $params->setLastPreventiveDate(new \DateTime());
            $this->manager->persist($params);
            $this->manager->flush();
        }

        // Gestion de l'enregistrement de la valeur du stock.
        // une fois par semaine-------.
        $lastStockValueDate = new \DateTime();
        $lastStockValueDate->setTimestamp(
            $params->getLastStockValueDate()->getTimestamp()
        );
        $lastStockValueDate->add($oneWeek);

        // Si la date du jour est >= d'une semaine à l'ancienne date.
        if ($today >= $lastStockValueDate) {
            $this->stockValueService->computeStockValue(
                $organisation,
                $organisationId,
                $params
            );
        }

        // ----------------------------------------------------------.
        // Récupération des utilisateurs pour l'affichage des photos.
        // Par organisation ET service.
        $users = $this->userRepository->findBy(
            [
                'organisation' => $organisationId,
                'service' => $serviceId,
                'active' => true,
            ],
        );

        // Compte des BT préventifs en retard.
        $lateBT = $this->workorderRepository->countLateBT($organisationId);

        // Compte des pièces à acheter
        $partsToBuy = $this->partRepository->countPartsToBuy($organisationId);

        // Compte de pièces en retard de livraison.
        $lateParts = $this->partRepository->countLateParts($organisationId);
        // dd($users);
        return $this->render(
            'default/index.html.twig',
            [
                'users' => $users,
                'lateBT' => $lateBT,
                'lateParts' => $lateParts,
                'partsToBuy' => $partsToBuy
            ]
        );
    }
}
