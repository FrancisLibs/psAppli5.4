<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Machine;
use App\Entity\Workorder;
use App\Form\WorkorderType;
use App\Data\SearchWorkorder;
use App\Form\SearchWorkorderForm;
use App\Service\OrganisationService;
use App\Repository\MachineRepository;
use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/work/order")
 */
class WorkorderController extends AbstractController
{
    protected $requestStack;
    protected $workorderRepository;
    protected $manager;
    protected $machineRepository;
    protected $templateRepository;
    protected $workorderStatusRepository;
    protected $organisation;


    public function __construct(
        MachineRepository $machineRepository,
        WorkorderRepository $workorderRepository,
        WorkorderStatusRepository $workorderStatusRepository,
        TemplateRepository $templateRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack,
        OrganisationService $organisation,
    ) {
        $this->workorderRepository = $workorderRepository;
        $this->machineRepository = $machineRepository;
        $this->templateRepository = $templateRepository;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->organisation = $organisation;
    }


    /**
     * Liste des bt
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'work_order_index', methods: ["GET"])]
    public function index(Request $request): Response
    {
        // Reset session
        $session = $this->requestStack->getSession();
        $session->remove('machines');
        $session->remove('panier');

        $data = new SearchWorkorder();
        $data->page = $request->get('page', 1);
        $data->organisation = $this->organisation->getOrganisation()->getId();
        $form = $this->createForm(SearchWorkorderForm::class, $data);
        $form->handleRequest($request);
        $workorders = $this->workorderRepository->findSearch($data);

        if ($request->isXmlHttpRequest() === true)
        // if ($request->get('ajax')) 
        {
            return new JsonResponse(
                [
                    'content'       =>  $this->renderView(
                        'workorder/_workorders.html.twig',
                        ['workorders' => $workorders]
                    ),
                    'sorting'       =>  $this->renderView(
                        'workorder/_sorting.html.twig',
                        ['workorders' => $workorders]
                    ),
                    'pagination'    =>  $this->renderView(
                        'workorder/_pagination.html.twig',
                        ['workorders' => $workorders]
                    ),
                ]
            );
        }
        return $this->render(
            'workorder/index.html.twig',
            [
                'workorders' =>  $workorders,
                'form'  =>  $form->createView(),
            ]
        );
    }

    /**
     * Nouveau BT
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/new/{id?}', name: 'work_order_new', methods: ["GET", "POST"])]
    public function new(Request $request, Machine $machine = null): Response
    {
        $session = $this->requestStack->getSession();
        $userParams = [];
        $user = $this->getUser();
        $userParams[] = $this->organisation->getOrganisation();
        $userParams[] = $this->organisation->getService();

        $workorder = new Workorder();
        $workorder->setPreventive(false);

        // Si une machine a été mise en session, elle est pour le BT
        $machines = $session->get('machines', []);
        if ($machines) {
            $machine = $this->machineRepository->find($machines[0]);
            $workorder->addMachine($machine);
        }
        // Initialisation of the workorder
        $workorder
            ->setStartDate(new \Datetime())
            ->setEndDate(new \Datetime())
            ->setStartTime(new \Datetime())
            ->setEndTime(new \Datetime())
            ->setDurationDay(0)
            ->setDurationHour(0)
            ->setDurationMinute(0)
            ->setStopTimeHour(0)
            ->setStopTimeMinute(0)
            ->setOperationPrice(0)
            ->setPartsPrice(0);

        //Creation of the form
        $form = $this->createForm(
            WorkorderType::class,
            $workorder,
            ['userParams' => $userParams]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorder->setUser($user);
            $workorder->setOrganisation($this->organisation->getOrganisation());
            $workorder->setCreatedAt(new \DateTime());
            $workorder->setPreventive(false);

            if ($machine) {
                $workorder->addMachine($machine);
            }
            $session->remove('machines');

            if ($workorder->getMachines()->isEmpty()) {
                $this->addFlash('error', 'Il n\'y a pas de machine dans le BT');
                return $this->redirectToRoute('work_order_new');
            }

            // Contrôle BT terminé 
            $minute = $workorder->getDurationMinute();
            $hour = $workorder->getDurationHour();
            $day = $workorder->getDurationDay();
            $request = $workorder->getRequest();
            $implementation = $workorder->getImplementation();
            $machine = $workorder->getMachines();

            $status = $this->workorderStatusRepository->findOneBy(
                ['name' => 'EN_COURS']
            );
            if (($minute > 0 || $hour > 0 || $day > 0)
                && !empty($request)
                && !empty($implementation)
                && !empty($machine)
            ) {
                $status = $this->workorderStatusRepository->findOneBy(
                    ['name' => 'TERMINE']
                );
            }

            $workorder->setWorkorderStatus($status);

            $this->manager->persist($workorder);
            $this->manager->flush();

            return $this->redirectToRoute(
                'work_order_show',
                [
                    'id' => $workorder->getId()
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'workorder/new.html.twig',
            [
                'workorder' => $workorder,
                'form' => $form,
            ]
        );
    }

    /**
     * Visualisation d'un BT
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'work_order_show', methods: ["GET"])]
    public function show(Workorder $workorder): Response
    {
        return $this->render(
            'workorder/show.html.twig',
            [
                'workorder' => $workorder,
            ]
        );
    }

    /**
     * Edition d'un BT
     */
    #[IsGranted('ROLE_USER')]
    #[Route(
        '/edit/{id}/{machine?}',
        name: 'work_order_edit',
        methods: ["GET", "POST"]
    )]
    public function edit(
        Request $request,
        Workorder $workorder,
        $machine = null
    ): Response {
        $userParams = [];
        $user = $this->getUser();
        $userParams[] = $this->organisation->getOrganisation();
        $userParams[] = $this->organisation->getService();

        // Lorsqu'il y a une machine en paramètre, 
        // on est dans le cas de l'édition de BT
        // et on veut remplacer la machine on efface donc 
        //l'actuelle et on la remplace par celle en paramètre
        if ($machine) {
            $workorderMachines = $workorder->getMachines();
            foreach ($workorderMachines as $workorderMachine) {
                $workorder->removeMachine($workorderMachine);
            }
            $id = $machine;
            $workorder->addMachine($this->machineRepository->find($id));
        }

        $form = $this->createForm(
            WorkorderType::class,
            $workorder,
            [
                'userParams' => $userParams,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Contrôle BT terminé 
            $machine = $workorder->getMachines();
            $minute = $workorder->getDurationMinute();
            $hour = $workorder->getDurationHour();
            $day = $workorder->getDurationDay();
            $request = $workorder->getRequest();
            $implementation = $workorder->getImplementation();

            if (($minute > 0 || $hour > 0 || $day > 0)
                && !empty($request)
                && !empty($implementation)
                && !empty($machine)
            ) {
                $status = $this->workorderStatusRepository->findOneBy(
                    ['name' => 'TERMINE']
                );
            } else {
                $status = $this->workorderStatusRepository->findOneBy(
                    ['name' => 'EN_COURS']
                );
            }

            $workorder->setWorkorderStatus($status);

            $this->manager->flush();

            return $this->redirectToRoute(
                'work_order_show',
                [
                    'id' => $workorder->getId()
                ],
                Response::HTTP_SEE_OTHER
            );
        }
        return $this->renderForm(
            'workorder/edit.html.twig',
            [
                'workorder' => $workorder,
                'form' => $form,
                'edit' => true,
            ]
        );
    }

    /**
     * Affichage de la liste des pièces détachées pour selection
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/addPart/{id}/{mode?}', name: 'work_order_add_part', methods: ["GET"])]
    public function addPart(
        Request $request,
        int $id,
        ?string $mode = null
    ): Response {
        $session = $this->requestStack->getSession();
        // Vidange du panier
        $session->remove('panier');
        $session->remove('data');

        $workorder = $this->workorderRepository->find($id);

        return $this->redirectToRoute(
            'part_index',
            [
                'mode' => $mode,
                'documentId' => $id,
            ]
        );
    }

    /**
     * Suppression d'un BT
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'work_order_delete', methods: ["POST"])]
    public function delete(Request $request, Workorder $workorder): Response
    {
        if ($this->isCsrfTokenValid(
            'delete' . $workorder->getId(),
            $request->request->get('_token')
        )) {
            $this->manager->remove($workorder);
            $this->manager->flush();
        }

        return $this->redirectToRoute(
            'work_order_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    /**
     * Cloture des BT et calcul de la prochaine date pour les BT préventifs.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/closing/{id}', name: 'work_order_closing')]
    public function closing(Workorder $workorder): RedirectResponse
    {
        if (
            $workorder->getDurationDay() > 0
            || $workorder->getDurationHour() > 0
            || $workorder->getDurationMinute() > 0
        ) {
            // Si cloture d'un BT préventif, calcule de la prochaine date du template
            if ($workorder->getPreventive()) {
                // récupération du template
                $templateNumber = $workorder->getTemplateNumber();
                $template = $this->templateRepository->findOneBy(
                    ['templateNumber' => $templateNumber]
                );
                // Récup de la période en jours et transformation en secondes
                $period = $template->getPeriod() * 24 * 60 * 60;
                // Prochaine date en secondes
                $nextDate = $workorder->getPreventiveDate()->getTimeStamp();
                // Aujourd'hui en secondes
                $today = (new \DateTime())->getTimeStamp();
                $date = new \DateTime();

                if ($template->getSliding()) {
                    // Si préventif glissant, ajout de la période à la date du jour
                    $date->setTimestamp($today + $period);
                    //dd($date->format('d-m-Y'));
                    $template->setNextDate($date);
                } else {
                    // Sinon, ajout de la période à la l'ancienne date
                    $date->setTimestamp($nextDate + $period);
                    //dd('ng', $date);
                    $template->setNextDate($date);
                }
            }

            $status = $this->workorderStatusRepository->findOneBy(
                ['name' => 'CLOTURE']
            );
            $workorder->setWorkorderStatus($status);
        } else {
            $this->addFlash(
                'error',
                'Merci de compléter les infos de durée d\'intervention !'
            );
            return $this->redirectToRoute(
                'work_order_edit',
                [
                    'id' => $workorder->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }
        $this->manager->flush();

        return $this->redirectToRoute(
            'work_order_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    /**
     * Impression d'un BT
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/pdf/{id}', name: 'pdf_workorder')]
    public function pdfAction(Workorder $workorder)
    {
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView(
            'workorder/print.html.twig',
            [
                'workorder' => $workorder
            ]
        );

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Courier');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($options);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Render the HTML as PDF
        $dompdf->render();
        //dd($dompdf);

        // Output the generated PDF to Browser (force download)
        $dompdf->stream();

        return new Response(
            '',
            200,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}
