<?php

namespace App\Controller;

use App\Entity\Workorder;
use App\Form\WorkorderType;
use App\Data\SearchWorkorder;
use App\Form\WorkorderEditType;
use App\Form\SearchWorkorderForm;
use App\Repository\PartRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/work/order")
 */
class WorkorderController extends AbstractController
{
    private $workorderRepository;
    private $manager;
    private $partRepository;

    public function __construct(WorkorderRepository $workorderRepository, EntityManagerInterface $manager, PartRepository $partRepository)
    {
        $this->workorderRepository = $workorderRepository;
        $this->manager = $manager;
        $this->partRepository = $partRepository;
    }

    /**
     * @Route("/", name="work_order_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     * 
     * @param Request $request
     * @return Response 
     */
    public function index(Request $request): Response
    {
        $data = new SearchWorkorder();
        $data->page = $request->get('page', 1);
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
     * @Route("/new", name="work_order_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function new(Request $request): Response
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $date = $dateTime;
        $workorder = new Workorder();
        $workorder->setCreatedAt($date);
        $workorder->setOrganisation($organisation);

        $form = $this->createForm(WorkorderType::class, $workorder, [
            'organisation' => $organisation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workorder->setUser($user);
            $workorder->setStatus($workorder::EN_COURS);
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
     * @Route("/{id}/edit", name="work_order_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, Workorder $workorder): Response
    {
        $workshop = $workorder->getMachine()->getWorkshop();
        $form = $this->createForm(WorkorderEditType::class, $workorder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @Route("/closing/{id}", name="work_order_closing")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function closing(Workorder $workorder): RedirectResponse
    {
        $workorder->setStatus(Workorder::CLOTURE);
        $this->manager->flush();

        return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
