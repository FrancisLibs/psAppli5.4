<?php

namespace App\Controller;

use App\Entity\OnCall;
use App\Form\OnCallType;
use App\Data\SearchOnCall;
use App\Form\SearchOnCallForm;
use App\Repository\OnCallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/onCall')]
class OnCallController extends AbstractController
{
    private $onCallRepository;

    public function __construct(OnCallRepository $onCallRepository)
    {
        $this->onCallRepository = $onCallRepository;
    }

    /**
     * Liste des rapports d'astreinte
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/', name: 'onCall_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation()->getId();
        $service = $user->getService()->getId();

        $data = new SearchOnCall();
        $data->page = $request->get('page', 1);

        $form = $this->createForm(SearchOnCallForm::class, $data);
        $form->handleRequest($request);

        $onCalls = $this->onCallRepository->findSearch($data, $organisation, $service);

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('onCalls/_onCalls.html.twig', ['onCalls' => $onCalls]),
                'sorting'       =>  $this->renderView('onCalls/_sorting.html.twig', ['onCalls' => $onCalls]),
                'pagination'    =>  $this->renderView('onCalls/_pagination.html.twig', ['onCalls' => $onCalls]),
            ]);
        }

        return $this->render('onCalls/index.html.twig', [
            'onCalls'  => $onCalls,
            'form'  =>  $form->createView(),
        ]);
    }

    #[Route('/new', name: 'onCall_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $onCall = new OnCall();

        $onCall->setUser($user)
            ->setCreatedAt(new \Datetime())
            ->setStatus(0)
            ->setCallDay(new \DateTime())
            ->setCallTime(new \DateTime());

        $form = $this->createForm(OnCallType::class, $onCall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($onCall);
            $entityManager->flush();

            return $this->redirectToRoute('onCall_show', [
                'id' => $onCall->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('onCalls/new.html.twig', [
            'onCall' => $onCall,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'onCall_show', methods: ['GET'])]
    public function show(OnCall $onCall): Response
    {
        return $this->render('onCalls/show.html.twig', [
            'onCall' => $onCall,
        ]);
    }

    #[Route('/{id}/edit', name: 'onCall_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Oncall $onCall, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OnCallType::class, $onCall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('onCall_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('onCalls/edit.html.twig', [
            'onCall' => $onCall,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'onCall_delete', methods: ['POST'])]
    public function delete(Request $request, Oncall $onCall, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $onCall->getId(), $request->request->get('_token'))) {
            $entityManager->remove($onCall);
            $entityManager->flush();
        }

        return $this->redirectToRoute('onCall_index', [], Response::HTTP_SEE_OTHER);
    }
}
