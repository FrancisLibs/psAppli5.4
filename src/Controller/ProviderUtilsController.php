<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Data\SelectProvider;
use App\Form\ProviderCleanType;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/providerUtils")
 */
class ProviderUtilsController extends AbstractController
{
    protected $providerRepository;
    protected $manager;
    protected $partRepository;
    protected $organisation;
    protected $deliveryNoteRepository;


    public function __construct(
        OrganisationService $organisation,
        PartRepository $partRepository,
        ProviderRepository $providerRepository,
        EntityManagerInterface $manager,
        DeliveryNoteRepository $deliveryNoteRepository,
    ) {
        $this->organisation = $organisation;
        $this->partRepository = $partRepository;
        $this->providerRepository = $providerRepository;
        $this->manager = $manager;
        $this->deliveryNoteRepository = $deliveryNoteRepository;
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/providerClean', name: 'provider_clean', methods: ["GET", "POST"])]
    public function action(Request $request): Response
    {
        $organisationId = $this->organisation->getOrganisation()->getId();

        $selectProvider = new SelectProvider();

        $form = $this->createForm(ProviderCleanType::class, $selectProvider, [
            'action' => $this->generateUrl('provider_clean'),
            'method' => 'POST',
            'attr' => array(
                'id' => 'provider_clean',
            )
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $providerToKeep = $selectProvider->getProviderToKeep();
            $providerToReplaceId = $selectProvider->getProviderToReplace()->getId();

            $parts = $this->partRepository->findPartsByProvider($organisationId, $providerToReplaceId);

            if ($parts) {
                foreach ($parts as $part) {
                    $part->setProvider($providerToKeep);
                    $this->manager->persist($part);
                }

                $this->manager->flush();
            }

            $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNoteByProvider($organisationId, $providerToReplaceId);
            if ($deliveryNotes) {
                foreach ($deliveryNotes as $deliveryNote) {
                    $deliveryNote->setProvider($providerToKeep);

                    $this->manager->persist($deliveryNote);
                }

                $this->manager->flush();
            }
        }

        return $this->render('provider/cleanProvider.html.twig', [
            'form'  =>  $form->createView(),
        ]);
    }

    #[Route('/get-entity-info/{id}', name: 'get_entity_info', methods: ["GET"])]
    public function getEntityInfo(Provider $provider)
    {
        $providerId = $provider->getId();
        $organisationId = $this->organisation->getOrganisation()->getId();
        $providerParts = $this->partRepository->findPartsByProvider($organisationId, $providerId);
        $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNoteByProvider($organisationId, $providerId);
        // dd($deliveryNotes);
        $parts = array();
        $data = array();

        $prov = [
            'name' => $provider->getname(),
            'code' => $provider->getCode(),
            'id' => $provider->getId(),
            'adress' => $provider->getAddress(),
            'postalCode' => $provider->getPostalCode(),
            'city' => $provider->getCity(),
            'activity' => $provider->getActivity(),
        ];

        if ($providerParts !== null) {
            $par = array();
            foreach ($providerParts as $part) {
                $par = array(
                    'id' => $part->getId(),
                    'code' => $part->getCode(),
                    'designation' => $part->getDesignation(),
                );
                $parts[] = $par;
            }
        }

        if ($deliveryNotes !== null) {
            $notes = array();
            foreach ($deliveryNotes as $deliveryNote) {
                $note = array(
                    'id' => $deliveryNote->getId(),
                    'number' => $deliveryNote->getNumber(),
                );
                $notes[] = $note;
            }
        }

        $data = array();

        if ($prov !== null) {
            $data['provider'] = $prov;
        }

        if ($parts !== null) {
            $data['parts'] = $parts;
        }

        if ($notes !== null) {
            $data['deliveryNotes'] = $notes;
        }

        return new JsonResponse($data);
    }
}
