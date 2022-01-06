<?php

namespace App\Controller;

use App\Entity\DeliveryNote;
use App\Form\DeliveryNoteType;
use App\Data\SearchDeliveryNote;
use App\Form\SearchDeliveryNoteForm;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/delivery/note")
 */
class DeliveryNoteController extends AbstractController
{
    private $manager;
    private $requestStack;
    private $deliveryNoteRepository;
    private $providerRepository;

    public function __construct(
        EntityManagerInterface $entityManagerInterface, 
        RequestStack $requestStack, 
        DeliveryNoteRepository $deliveryNoteRepository,
        ProviderRepository $providerRepository
    )
    {
        $this->manager = $entityManagerInterface;
        $this->requestStack = $requestStack;
        $this->deliveryNoteRepository = $deliveryNoteRepository;
        $this->providerRepository = $providerRepository;
    }

    /**
     * @Route("/", name="delivery_note_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(Request $request, DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        $data = new SearchDeliveryNote();

        $organisation = $this->getUser()->getOrganisation();
        $data->organisation = $organisation;

        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchDeliveryNoteForm::class, $data);
        $form->handleRequest($request);
        $deliveryNotes = $this->deliveryNoteRepository->findSearch($data);

        return $this->render('delivery_note/index.html.twig', [
            'delivery_notes' => $deliveryNotes,
            'form'          =>  $form->createView(),
        ]);
    }

    /**
     * @Route("/new/{providerId?}", name="delivery_note_new", methods={"GET","POST"})
     */
    public function new(Request $request, ?int $providerId = null): Response
    {
        $user = $this->getUser();
        $organisation = $user->getOrganisation();
        $session = $this->requestStack->getSession();

        // Gestion du fournisseur
        if ($providerId) {
            $session->set('provider', $providerId);
        } else {
            $providerId = $session->get('provider', null);
        }
        if ($providerId) {
            $provider = $this->providerRepository->findOneBy(['id' => $providerId]);
        }

        $deliveryNote = new DeliveryNote();
        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deliveryNote->setProvider($provider);
            $deliveryNote->setOrganisation($organisation);
            $this->manager->persist($deliveryNote);
            $this->manager->flush();

            return $this->redirectToRoute('delivery_note_show', [
                'id' => $deliveryNote->getId(),
            ], Response::HTTP_SEE_OTHER);
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
        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote);
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
        if ($this->isCsrfTokenValid('delete' . $deliveryNote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($deliveryNote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('delivery_note_index', [], Response::HTTP_SEE_OTHER);
    }
}
