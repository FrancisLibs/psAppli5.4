<?php

namespace App\Controller;

use App\Entity\OnCall;
use App\Form\OnCallType;
use App\Data\SearchOnCall;
use App\Form\SearchOnCallForm;
use App\Repository\OnCallRepository;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/onCall')]
class OnCallController extends AbstractController
{
    private $_onCallRepository;
    private $_organisation;

    public function __construct(
        OrganisationService $organisation, 
        OnCallRepository $onCallRepository
    ) {
        $this->_onCallRepository = $onCallRepository;
        $this->_organisation = $organisation;
    }

    /**
     * Liste des rapports d'astreinte
     *
     * @param  Request $request
     * @return Response
     * 
     * @Security("is_granted('ROLE_USER')")
     */
    #[Route('/', name: 'onCall_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $organisation = $this->_organisation->getOrganisation();
        $service = $user->getService()->getId();

        $data = new SearchOnCall();
        $data->page = $request->get('page', 1);

        $form = $this->createForm(SearchOnCallForm::class, $data);
        $form->handleRequest($request);

        $onCalls = $this->_onCallRepository->findSearch(
            $data, 
            $organisation, 
            $service
        );

        if ($request->get('ajax')) {
            return new JsonResponse(
                [
                'content'       =>  $this->renderView(
                    'onCalls/_onCalls.html.twig', 
                    ['onCalls' => $onCalls]
                ),
                'sorting'       =>  $this->renderView(
                    'onCalls/_sorting.html.twig', 
                    ['onCalls' => $onCalls]
                ),
                'pagination'    =>  $this->renderView(
                    'onCalls/_pagination.html.twig', 
                    ['onCalls' => $onCalls]
                ),
                ]
            );
        }

        return $this->render(
            'onCalls/index.html.twig', [
            'onCalls'  => $onCalls,
            'form'  =>  $form->createView(),
            ]
        );
    }

    /**
     * New onCall report
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'onCall_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $onCall = new OnCall();

        $onCall->setUser($user)
            ->setCreatedAt(new \Datetime())
            ->setStatus(0)
            ->setCallDay(new \DateTime())
            ->setCallTime(new \DateTime())
            ->setDurationHours(0)
            ->setDurationMinutes(0)
            ->setTravelhours(0)
            ->setTravelMinutes(0);

        $form = $this->createForm(OnCallType::class, $onCall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($onCall);
            $entityManager->flush();

            return $this->redirectToRoute(
                'onCall_show', [
                'id' => $onCall->getId(),
                ], Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'onCalls/new.html.twig', [
            'onCall' => $onCall,
            'form' => $form,
            ]
        );
    }

    /**
     * Show onCall report
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'onCall_show', methods: ['GET'])]
    public function show(OnCall $onCall): Response
    {
        return $this->render(
            'onCalls/show.html.twig', [
            'onCall' => $onCall,
            ]
        );
    }

    /**
     * Edit onCall report
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'onCall_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Oncall $onCall, 
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(OnCallType::class, $onCall);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute(
                'onCall_index', 
                [], 
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'onCalls/edit.html.twig', [
            'onCall' => $onCall,
            'form' => $form,
            ]
        );
    }

    /**
     * Delete onCall report
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'onCall_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Oncall $onCall, 
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $onCall->getId(), 
            $request->request->get('_token')
        )
        ) {
            $entityManager->remove($onCall);
            $entityManager->flush();
        }

        return $this->redirectToRoute('onCall_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Change status to onCall
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/ok/{id}', name: 'onCall_ok', methods: ['GET'])]
    public function ok(
        Oncall $onCall, 
        EntityManagerInterface $entityManager
    ): Response {
        $onCall->setStatus(1)
            ->setTransmitted(new \Datetime());

        $entityManager->flush();

        return $this->redirectToRoute('onCall_index', [], Response::HTTP_SEE_OTHER);
    }
}
