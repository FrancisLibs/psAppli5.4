<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Entity\Template;
use App\Form\TemplateType;
use App\Data\SearchTemplate;
use App\Form\SearchTemplateForm;
use App\Repository\MachineRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/preventive")
 */
class PreventiveController extends AbstractController
{
    private $manager;
    private $requestStack;
    private $machineRepository;
    private $templateRepository;

    public function __construct(
        WorkorderStatusRepository $workorderStatusRepository,
        TemplateRepository $templateRepository,
        MachineRepository $machineRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack
    ) {
        $this->manager = $manager;
        $this->templateRepository = $templateRepository;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->requestStack = $requestStack;
        $this->machineRepository = $machineRepository;
    }

    /**
     * Liste des BT préventifs
     * 
     * @Route("/", name="template_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request 
     * @return Response 
     */
    public function index(Request $request): Response
    {
        // Vidange de la session s'il reste ds machines dedans
        $this->emptyMachineCart($request);
        $this->emptySearchMachine($request);


        $data = new SearchTemplate();

        $data->page = $request->get('page', 1);
        $data->organisation = $this->getUser()->getOrganisation();

        $form = $this->createForm(SearchTemplateForm::class, $data);

        $form->handleRequest($request);

        $templates = $this->templateRepository->findTemplates($data);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('preventive/_templates.html.twig', ['templates' => $templates]),
                'sorting'       =>  $this->renderView('preventive/_sorting.html.twig', ['templates' => $templates]),
                'pagination'    =>  $this->renderView('preventive/_pagination.html.twig', ['templates' => $templates]),
            ]);
        }
        return $this->render('preventive/index.html.twig', [
            'templates' =>  $templates,
            'form'  =>  $form->createView(),
        ]);
    }

    /**
     * Création d'un template préventif
     * 
     * @Route("/new/{id?}", name="template_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Machine $machine
     * @param Request 
     * @return Response
     */
    public function create(Request $request, Machine $machine = null): Response
    {
        // Récupération des machines lors d'un BT préventif
        $machinesWithData = [];
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        if ($machines) {
            foreach ($machines as $id) {
                $machinesWithData[] = $this->machineRepository->find($id);
            }
        }
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $template = new Template();
        $template->setCreatedAt(new \DateTime());
        $template->setOrganisation($organisation);
        $template->setUser($user);

        $form = $this->createForm(TemplateType::class, $template);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Contrôle si machine en session
            $machines = $session->get('machines', []);
            if (!empty($machines)) {
                // Ajout des machines au BT préventif
                foreach ($machines as $id) {
                    $template->addMachine($this->machineRepository->find($id));
                }

                // Suppression des machines en session
                $this->emptyMachineCart($request);
                // Suppression de la classe de recherche en session
                $this->emptySearchMachine($request);

                // Numéro de template
                $lastTemplate = $this->templateRepository->findLastTemplate($organisation);
                if ($lastTemplate) {
                    $lastNumber = $lastTemplate->getTemplateNumber();
                    $template->setTemplateNumber($lastNumber + 1);
                } else {
                    $template->setTemplateNumber(1);
                }
                // Activation du template
                $template->setActive(true);

                $this->manager->persist($template);
                $this->manager->flush();

                return $this->render('preventive/show.html.twig', [
                    'template' => $template
                ]);
            }
            $this->addFlash('error', 'Il n\'y a pas de machine dans le BT');
        }
        return $this->renderForm('preventive/new.html.twig', [
            'template' => $template,
            'form' => $form,
            'mode' => 'newPreventive',
            'machinesWithData' => $machinesWithData,
        ]);
    }

    /**
     * @Route("/{id}", name="template_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Template $template): Response
    {
        return $this->render('preventive/show.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/edit/{id}/{mode?}", name="template_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Template $template, ?string $mode): Response
    {
        if ($mode == 'editPreventive') {
            // Attribution des éventuelles machines en session au BT préventif
            $session = $this->requestStack->getSession();
            $machines = $session->get('machines');
            if ($machines) {
                foreach ($machines as $key => $id) {
                    $machine = $this->machineRepository->find($id);
                    $template->addMachine($machine);
                    unset($machines[$key]);
                }
                $session->set('machines', $machines);
                $this->manager->flush();
            }
        }
        $form = $this->createForm(TemplateType::class, $template);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérification si machines dans le BT
            $machines = $template->getMachines();
            if (!$machines->isEmpty()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute(
                    'template_show',
                    [
                        'id' => $template->getId()
                    ],
                    Response::HTTP_SEE_OTHER
                );
            }
            $this->addFlash('error', 'Il n\'y a pas de machine dans le BT');
        }
        return $this->renderForm('preventive/edit.html.twig', [
            'template' => $template,
            'form' => $form,
            'mode' => 'editPreventive',
        ]);
    }

    /**
     * Enlever une machine du BT
     * 
     * @Route("/machine/remove/{id}/{machine}", name="preventive_machine_remove", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @return Response 
     */
    public function removeMachine(Template $template, Machine $machine)
    {
        $machine = $this->machineRepository->find($machine);
        $template->removeMachine($machine);
        $this->manager->flush();

        return $this->redirectToRoute(
            'template_edit',
            [
                'id' => $template->getId()
            ],
            Response::HTTP_SEE_OTHER
        );
    }

    public function emptyMachineCart(Request $request)
    {
        $session = $this->requestStack->getSession();
        $machines = $session->get('machines', []);
        foreach ($machines as $cle => $value) {
            unset($machines[$cle]);
        }
        $session->set('machines', $machines);
        return;
    }

    public function emptySearchMachine(Request $request)
    {
        $session = $this->requestStack->getSession();
        $session->remove('dataMachinePreventive');
        return;
    }

    /**
     * @Route("/{id}", name="template_delete", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Template $template): Response
    {
        if ($this->isCsrfTokenValid('delete' . $template->getId(), $request->request->get('_token'))) {
            $template->setActive(false);
            $this->manager->flush();
        }

        return $this->redirectToRoute('template_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("copy_template/{id}", name="copy_template", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function copyTemplate(Request $request, Template $template): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();

        $newTemplate = new Template();

        $newTemplate->setCreatedAt(new \DateTime())
            ->setDaysBefore($template->getDaysBefore())
            ->setDaysBeforeLate($template->getDaysBeforeLate())
            ->setDuration($template->getDuration())
            ->setOrganisation($organisation)
            ->setPeriod($template->getPeriod())
            ->setRemark($template->getRemark())
            ->setRequest($template->getRequest())
            ->setSliding($template->getSliding())
            ->setUser($user)
            ->setActive(true)
            ->setNextDate(new \DateTime());

        // Numéro de template
        $lastTemplate = $this->templateRepository->findLastTemplate($organisation);
        if ($lastTemplate) {
            $lastNumber = $lastTemplate->getTemplateNumber();
            $newTemplate->setTemplateNumber($lastNumber + 1);
        } else {
            $newTemplate->setTemplateNumber(1);
        }

        $this->manager->persist($newTemplate);
        $this->manager->flush();


        return $this->redirectToRoute('template_edit', [
            'id'    =>  $newTemplate->getId(),
        ]);
    }
}