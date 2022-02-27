<?php

namespace App\Controller;

use Knp\Snappy\Pdf;
use App\Entity\Machine;
use App\Entity\Workorder;
use App\Form\WorkorderType;
use App\Data\SearchWorkorder;
use App\Form\WorkorderEditType;
use App\Form\SearchWorkorderForm;
use App\Repository\PartRepository;
use App\Repository\MachineRepository;
use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\WorkorderStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/work/order")
 */
class WorkorderController extends AbstractController
{
    private $requestStack;
    private $workorderRepository;
    private $manager;
    private $partRepository;
    private $machineRepository;
    private $templateRepository;
    private $workorderStatusRepository;
    private $pdf;

    public function __construct(
        Pdf $pdf,
        MachineRepository $machineRepository,
        WorkorderRepository $workorderRepository,
        TemplateRepository $templateRepository,
        PartRepository $partRepository,
        WorkorderStatusRepository $workorderStatusRepository,
        EntityManagerInterface $manager,
        RequestStack $requestStack
    ) {
        $this->workorderRepository = $workorderRepository;
        $this->partRepository = $partRepository;
        $this->machineRepository = $machineRepository;
        $this->templateRepository = $templateRepository;
        $this->pdf = $pdf;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
    }

    /**
     * Liste des bt
     * 
     * @Route("/", name="work_order_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request $request
     * @return Response 
     */
    public function index(Request $request): Response
    {
        // Reset session
        $session = $this->requestStack->getSession();
        $session->remove('machines');
        $session->remove('panier');

        $data = new SearchWorkorder();
        $data->page = $request->get('page', 1);
        $data->organisation = $this->getUser()->getOrganisation()->getId();
        $form = $this->createForm(SearchWorkorderForm::class, $data);
        $form->handleRequest($request);
        $workorders = $this->workorderRepository->findSearch($data);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('workorder/_workorders.html.twig', ['workorders' => $workorders]),
                'sorting'       =>  $this->renderView('workorder/_sorting.html.twig', ['workorders' => $workorders]),
                'pagination'    =>  $this->renderView('workorder/_pagination.html.twig', ['workorders' => $workorders]),
            ]);
        }
        return $this->render('workorder/index.html.twig', [
            'workorders' =>  $workorders,
            'form'  =>  $form->createView(),
        ]);
    }

    /**
     * @Route("/new/{id?}", name="work_order_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request, Machine $machine = null): Response
    {
        $session = $this->requestStack->getSession();
        $user = $this->getUser();
        $organisation = $user->getOrganisation();

        $workorder = new Workorder();
        $workorder->setPreventive(false);
        $workorder->setCreatedAt(new \DateTime());

        // Si une machine a été mise en session, elle est pour le BT
        $machines = $session->get('machines', []);
        if ($machines) {
            $machine = $this->machineRepository->find($machines[0]);
            $workorder->addMachine($machine);
        }

        $form = $this->createForm(WorkorderType::class, $workorder, [
            'organisation' => $organisation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorder->setUser($user);
            $workorder->setOrganisation($organisation);
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
            $machine = $workorder->getMachines();
            $minute = $workorder->getDurationMinute();
            $hour = $workorder->getDurationHour();
            $day = $workorder->getDurationDay();
            $request = $workorder->getRequest();
            $implementation = $workorder->getImplementation();

            if (($minute > 0 || $hour > 0 || $day > 0) && !empty($request)  && !empty($implementation) && !empty($machine)) {
                $status = $this->workorderStatusRepository->findOneBy(['name' => 'TERMINE']);
            } else {
                $status = $this->workorderStatusRepository->findOneBy(['name' => 'EN_COURS']);
            }
            $workorder->setWorkorderStatus($status);

            $this->manager->persist($workorder);
            $this->manager->flush();

            return $this->redirectToRoute('work_order_show', [
                'id' => $workorder->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('workorder/new.html.twig', [
            'workorder' => $workorder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="work_order_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Workorder $workorder): Response
    {
        return $this->render('workorder/show.html.twig', [
            'workorder' => $workorder,
        ]);
    }

    /**
     * @Route("/edit/{id}/{machine?}", name="work_order_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Workorder $workorder, $machine = null): Response
    {
        // Lorsqu'il y a une machine en paramètre, on est dans le cas de l'édition de BT
        // et on veut remplacer la machine on efface donc l'actuelle et on la remplace par celle en paramètre
        if ($machine) {
            $workorderMachines = $workorder->getMachines();
            foreach ($workorderMachines as $workorderMachine) {
                $workorder->removeMachine($workorderMachine);
            }
            $id = $machine;
            $workorder->addMachine($this->machineRepository->find($id));
        }

        $form = $this->createForm(WorkorderEditType::class, $workorder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Contrôle BT terminé 
            $machine = $workorder->getMachines();
            $minute = $workorder->getDurationMinute();
            $hour = $workorder->getDurationHour();
            $day = $workorder->getDurationDay();
            $request = $workorder->getRequest();
            $implementation = $workorder->getImplementation();

            if (($minute > 0 || $hour > 0 || $day > 0) && !empty($request)  && !empty($implementation) && !empty($machine)) {
                $status = $this->workorderStatusRepository->findOneBy(['name' => 'TERMINE']);
            } else {
                $status = $this->workorderStatusRepository->findOneBy(['name' => 'EN_COURS']);
            }
            $workorder->setWorkorderStatus($status);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'work_order_show',
                [
                    'id' => $workorder->getId()
                ],
                Response::HTTP_SEE_OTHER
            );
        }
        return $this->renderForm('workorder/edit.html.twig', [
            'workorder' => $workorder,
            'form' => $form,
            'edit' => true,
        ]);
    }

    /**
     * Affichage de la liste des pièces pour selection
     *
     * @Route("/addPart/{id}/{mode?}", name="work_order_add_part", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function addPart(Request $request, int $id, ?string $mode = null): Response
    {
        $session = $this->requestStack->getSession();

        $session->remove('panier');
        $session->remove('data');

        return $this->redirectToRoute('part_index', [
            'mode' => $mode,
            'documentId' => $id,
        ]);
    }


    /**
     * @Route("/{id}", name="work_order_delete", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Workorder $workorder): Response
    {
        if ($this->isCsrfTokenValid('delete' . $workorder->getId(), $request->request->get('_token'))) {
            $this->manager->remove($workorder);
            $this->manager->flush();
        }

        return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Cloture des BT. Et calcul de la prochaine date pour les BT préventifs.
     * 
     * @Route("/closing/{id}", name="work_order_closing")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function closing(Workorder $workorder): RedirectResponse
    {

        if ($workorder->getDurationDay() > 0 || $workorder->getDurationHour() > 0 || $workorder->getDurationMinute() > 0) {
            // Si cloture d'un préventif, réarmement du template pour la prochaine utilisation
            if ($workorder->getPreventive()) {
                // récupération du template
                $templateNumber = $workorder->getTemplateNumber();
                $template = $this->templateRepository->findOneBy(['templateNumber' => $templateNumber]);
                $period = $template->getPeriod() * 24 * 60 * 60;
                $oldNextDate = $template->getNextDate()->getTimeStamp();
                $today = (new \DateTime())->getTimeStamp();

                // Si glissant, affection de la date du jour à la période
                $date = new \DateTime();
                if ($template->getSliding()) {
                    $date->setTimestamp($today + $period);
                    $template->setNextDate($date);
                } else { // Sinon, affection de l'ancienne date à la période
                    $date->setTimestamp($oldNextDate + $period);
                    $template->setNextDate($date);
                }
            }

            $status = $this->workorderStatusRepository->findOneBy(['name' => 'CLOTURE']);
            $workorder->setWorkorderStatus($status);
        } else {
            $this->addFlash('error', 'Merci mon lapin, de compléter les infos de durée d\'intervention !');
            return $this->redirectToRoute('work_order_edit', [
                'id' => $workorder->getId(),
            ], Response::HTTP_SEE_OTHER);
        }
        $this->manager->flush();

        return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Impression d'un BT
     * 
     * @Route("/pdf/{id}", name="pdf_workorder")
     * @Security("is_granted('ROLE_USER')")
     * 
     */
    public function pdfAction(\Knp\Snappy\Pdf $knpSnappyPdf, Workorder $workorder)
    {
        $html = $this->renderView('workorder/show.html.twig', [
            'workorder' => $workorder,
        ]);

        $this->pdf->setOption('enable-local-file-access', true);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'test.pdf'
        );
    }
}
