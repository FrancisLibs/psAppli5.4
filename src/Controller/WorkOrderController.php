<?php

namespace App\Controller;

use App\Entity\Workorder;
use App\Form\WorkorderType;
use App\Data\SearchWorkorder;
use App\Form\SearchWorkorderForm;
use App\Repository\WorkorderRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/work/order")
 */
class WorkorderController extends AbstractController
{
    private $workorderRepository;
    private $manager;

    public function __construct(WorkorderRepository $workorderRepository, EntityManagerInterface $manager)
    {
        $this->workorderRepository = $workorderRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/", name="work_order_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $data = new SearchWorkorder();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchWorkorderForm::class, $data);
        $form->handleRequest($request);
        $workorders = $this->workorderRepository->findSearch($data);
        //dd($parts);
        // if ($request->get('ajax')) {
        //     return new JsonResponse([
        //         'content'       =>  $this->renderView('ident/_idents.html.twig', ['idents' => $idents]),
        //         'sorting'       =>  $this->renderView('ident/_sorting.html.twig', ['idents' => $idents]),
        //         'pagination'    =>  $this->renderView('ident/_pagination.html.twig', ['idents' => $idents]),
        //     ]);
        // }
        return $this->render('workorder/index.html.twig', [
            'workorders' =>  $workorders,
            'form'  =>  $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="work_order_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('Europe/paris'));
        dd($dateTime->format('d-m-Y H:m'));
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
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
     */
    public function show(Workorder $workorder): Response
    {
        return $this->render('workorder/show.html.twig', [
            'workorder' => $workorder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="work_order_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Workorder $workorder): Response
    {

        $form = $this->createForm(WorkorderType::class, $workorder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('workorder/edit.html.twig', [
            'workorder' => $workorder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="work_order_delete", methods={"POST"})
     */
    public function delete(Request $request, Workorder $workorder): Response
    {
        if ($this->isCsrfTokenValid('delete' . $workorder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($workorder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('work_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
