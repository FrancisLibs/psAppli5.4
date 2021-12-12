<?php

namespace App\Controller;

use App\Entity\DeliveryNote;
use App\Form\DeliveryNoteType;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/delivery/note")
 */
class DeliveryNoteController extends AbstractController
{
    private $manager;
    private $requestStack;
    private $providerRepository;

    public function __construct(EntityManagerInterface $entityManagerInterface, RequestStack $requestStack, ProviderRepository $providerRepository)
    {
        $this->manager = $entityManagerInterface;
        $this->requestStack = $requestStack;
        $this->providerRepository = $providerRepository;
    }

    /**
     * @Route("/", name="delivery_note_index", methods={"GET"})
     */
    public function index(DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        return $this->render('delivery_note/index.html.twig', [
            'delivery_notes' => $deliveryNoteRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{providerId?}", name="delivery_note_new", methods={"GET","POST"})
     */
    public function new(Request $request, ?int $providerId): Response
    {
        // Mise en session du fournisseur
        $session = $this->requestStack->getSession();
        $session->set('provider', $providerId);
        $provider = $this->providerRepository->findOneBy(['id' => $providerId]);

        $deliveryNote = new DeliveryNote();
        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($deliveryNote);
            $this->manager->flush();

            return $this->redirectToRoute('delivery_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('delivery_note/new.html.twig', [
            'delivery_note' => $deliveryNote,
            'form' => $form,
            'provider' => $provider,
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_note_show", methods={"GET"})
     */
    public function show(DeliveryNote $deliveryNote): Response
    {
        return $this->render('delivery_note/show.html.twig', [
            'delivery_note' => $deliveryNote,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="delivery_note_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, DeliveryNote $deliveryNote): Response
    {
        $form = $this->createForm(DeliveryNote1Type::class, $deliveryNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('delivery_note_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('delivery_note/edit.html.twig', [
            'delivery_note' => $deliveryNote,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="delivery_note_delete", methods={"POST"})
     */
    public function delete(Request $request, DeliveryNote $deliveryNote): Response
    {
        if ($this->isCsrfTokenValid('delete'.$deliveryNote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryNote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('delivery_note_index', [], Response::HTTP_SEE_OTHER);
    }
}
