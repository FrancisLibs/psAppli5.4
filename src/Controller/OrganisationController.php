<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Form\OrganisationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganisationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/organisation')]
class OrganisationController extends AbstractController
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_organisation_index', methods: ['GET'])]
    public function index(OrganisationRepository $organisationRepository): Response
    {
        return $this->render('organisation/index.html.twig', [
            'organisations' => $organisationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_organisation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $organisation = new Organisation();
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($organisation);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_organisation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('organisation/new.html.twig', [
            'organisation' => $organisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organisation_show', methods: ['GET'])]
    public function show(Organisation $organisation): Response
    {
        return $this->render('organisation/show.html.twig', [
            'organisation' => $organisation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_organisation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Organisation $organisation): Response
    {
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_organisation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('organisation/edit.html.twig', [
            'organisation' => $organisation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_organisation_delete', methods: ['POST'])]
    public function delete(Request $request, Organisation $organisation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$organisation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($organisation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_organisation_index', [], Response::HTTP_SEE_OTHER);
    }
}
