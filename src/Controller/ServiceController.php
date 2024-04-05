<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Service\OrganisationService;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/service')]
class ServiceController extends AbstractController
{
    protected $organisation;
    protected $serviceRepository;
    protected $manager;


    public function __construct(
        ServiceRepository $serviceRepository, 
        OrganisationService $organisation,
        EntityManagerInterface $manager,
    ) {
        $this->organisation = $organisation;
        $this->serviceRepository = $serviceRepository;
        $this->manager = $manager;
    }


    #[Route('/', name: 'service_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $services 
            = $this->serviceRepository->findAllServicesByOrganisation(
                $organisation->getId()
            );

        return $this->render(
            'service/index.html.twig', 
            [
                'services' => $services,
                'organisation' => $organisation,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'service_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request
    ): Response {
        $organisation = $this->organisation->getOrganisation();
        $service = new Service();
        $service->setOrganisation($organisation);
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($service);
            $this->manager->flush();

            return $this->redirectToRoute(
                'service_index', 
                [], 
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'service/new.html.twig', 
            [
            'service' => $service,
            'form' => $form,
            'organisation' => $organisation,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'service_show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        return $this->render(
            'service/show.html.twig', [
            'service' => $service,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'service_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Service $service, 
    ): Response {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute(
                'service_index', 
                [], 
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'service/edit.html.twig', [
            'service' => $service,
            'form' => $form,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'service_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Service $service, 
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$service->getId(), 
            $request->request->get('_token')
        )
        ) {
            $this->manager->remove($service);
            $this->manager->flush();
        }

        return $this->redirectToRoute('service_index', [], Response::HTTP_SEE_OTHER);
    }
}
