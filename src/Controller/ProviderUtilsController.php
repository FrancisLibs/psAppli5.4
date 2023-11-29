<?php

namespace App\Controller;

use App\Entity\Part;
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
                'id' => 'provider_clean_form',
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
            }

            $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNoteByProvider($organisationId, $providerToReplaceId);
            if ($deliveryNotes) {
                foreach ($deliveryNotes as $deliveryNote) {
                    $deliveryNote->setProvider($providerToKeep);

                    $this->manager->persist($deliveryNote);
                }
            }

            $this->manager->flush();
        }
        if ($request->isXmlHttpRequest()) {
            $providerToKeepId = $providerToKeep->getId();
            $providerParts = $this->partRepository->findPartsByProvider($organisationId, $providerToKeepId);
            $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNoteByProvider($organisationId, $providerToKeepId);

            $data = array();

            $data['provider'] = [
                'name' => $providerToKeep->getname(),
                'code' => $providerToKeep->getCode(),
                'id' => $providerToKeep->getId(),
                'adress' => $providerToKeep->getAddress(),
                'postalCode' => $providerToKeep->getPostalCode(),
                'city' => $providerToKeep->getCity(),
                'activity' => $providerToKeep->getActivity(),
            ];

            if ($providerParts !== null) {
                $data['parts'] = array_map(function ($part) {
                    return [
                        'id' => $part->getId(),
                        'code' => $part->getCode(),
                        'designation' => $part->getDesignation()
                    ];
                }, $providerParts);
            }

            if ($deliveryNotes !== null) {
                $data['deliveryNotes'] = array_map(function ($deliveryNote) {
                    return [
                        'id' => $deliveryNote->getId(),
                        'number' => $deliveryNote->getNumber()
                    ];
                }, $deliveryNotes);
            }

            return new JsonResponse($data);

        } else {
            return $this->render('provider/cleanProvider.html.twig', [
                'form'  =>  $form->createView(),
            ]);
        }
    }

    #[Route('/get-entity-info/{id}', name: 'get_entity_info', methods: ["GET"])]
    public function getEntityInfo(Provider $provider)
    {
        $providerId = $provider->getId();
        $organisationId = $this->organisation->getOrganisation()->getId();
        $providerParts = $this->partRepository->findPartsByProvider($organisationId, $providerId);
        $deliveryNotes = $this->deliveryNoteRepository->findDeliveryNoteByProvider($organisationId, $providerId);
        
        $data = array();

        $data['provider'] = [
            'name' => $provider->getname(),
            'code' => $provider->getCode(),
            'id' => $provider->getId(),
            'adress' => $provider->getAddress(),
            'postalCode' => $provider->getPostalCode(),
            'city' => $provider->getCity(),
            'activity' => $provider->getActivity(),
        ];

        if ($providerParts !== null) {
            $data['parts'] = array_map(function ($part) {
                return [
                    'id' => $part->getId(),
                    'code' => $part->getCode(),
                    'designation' => $part->getDesignation(),
                ];
            }, $providerParts);
        }

        if ($deliveryNotes !== null) {
            $data['deliveryNotes'] = array_map(function ($deliveryNote) {
                return [
                    'id' => $deliveryNote->getId(),
                    'number' => $deliveryNote->getNumber(),
                ];
            }, $deliveryNotes);
        }

        return new JsonResponse($data);
    }
}
