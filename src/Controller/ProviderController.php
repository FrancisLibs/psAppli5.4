<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Data\SearchProvider;
use App\Data\SelectProvider;
use App\Form\ProviderCleanType;
use App\Service\OrganisationService;
use App\Form\SearchProviderForm;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/provider")
 */
class ProviderController extends AbstractController
{
    protected $providerRepository;
    protected $manager;
    protected $organisation;


    public function __construct(
        ProviderRepository $providerRepository,
        EntityManagerInterface $manager,
        OrganisationService $organisation,
    ) {
        $this->providerRepository = $providerRepository;
        $this->manager = $manager;
        $this->organisation = $organisation;
    }


    #[Route('/show/{id}', name: 'provider_show', methods: ["GET"])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Provider $provider, Request $request): Response
    {
        $isAjaxRequest = $request->query->get('ajax');
        // Vérifier si la requête est une requête AJAX
        if ($isAjaxRequest) {
            $data = [
                'id' => $provider->getId(),
                'name' => $provider->getName(),
                'email' => $provider->getEmail(),
            ];
            // dd(new JsonResponse($data));
            return new JsonResponse($data);
        }

        return $this->render(
            'provider/show.html.twig',
            [
                'provider' => $provider,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'provider_edit', methods: ["GET", "POST"])]
    public function edit(Request $request, Provider $provider): Response
    {
        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $provider->setCode(strtoupper($provider->getCode()));
            $provider->setName(strtoupper($provider->getName()));
            $provider->setCity(strtoupper($provider->getCity()));

            $this->manager->flush();

            return $this->redirectToRoute(
                'provider_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'provider/edit.html.twig',
            [
                'provider' => $provider,
                'form' => $form,
            ]
        );
    }

    /**
     * New provider
     *
     * @param  Request $request
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'provider_new', methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $provider->setCode(strtoupper($provider->getCode()));
            $provider->setName(strtoupper($provider->getName()));
            $provider->setCity(strtoupper($provider->getCity()));
            $this->manager->persist($provider);
            $this->manager->flush();

            return $this->redirectToRoute(
                'provider_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm(
            'provider/new.html.twig',
            [
                'provider' => $provider,
                'form' => $form,
            ]
        );
    }

/**
 * Cette fonction est utilisée pour l'appel ajax dans le module priceRequest
 * Elle retourne la liste des fournisseurs
 *
 * @param Request $request
 * @return void
 */
    #[IsGranted('ROLE_USER')]
    #[Route('/list', name: 'provider_list', methods: ["GET"])]
    public function providerList(Request $request)
    {
        $organisationId = $this->organisation->getOrganisation()->getId();
        // Obtenir la liste des fournisseurs
        $providers = $this->providerRepository->findAllProviders($organisationId);

        // Convertir la liste en un tableau JSON
        $data = [];
        foreach ($providers as $provider) {
            $data[] = [
                'id' => $provider->getId(),
                'nom' => $provider->getName(),
            ];
        }
        return new JsonResponse($data);
    }

/**
     * Modification d'un fournisseur -> ajout d'une adresse email en ajax
     *
     * @param  Request $request
     * @return Response
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/email/{id}/{email}', name: 'provider_email', methods: ["GET"])]
    public function email(Request $request, Provider $provider, $email): Response
    {
        $provider->setEmail($email);
        $this->manager->persist($provider);
        $this->manager->flush();
        return new JsonResponse($email);
    }

    /**
     * Liste des fournisseurs
     * 
     * @param  Request $request
     * @return Response 
     */
    #[Route('/{mode?}/{documentId?}', name: 'provider_index', methods: ["GET"])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, ?string $mode, ?int $documentId = null): Response
    {
        $data = new SearchProvider();
        $data->page = $request->get('page', 1);

        $form = $this->createForm(SearchProviderForm::class, $data);
        $form->handleRequest($request);

        $providers = $this->providerRepository->findSearch($data);

        if ($request->get('ajax') && ($mode == 'selectProvider')) {
            return new JsonResponse(
                [
                    'content' => $this->renderView(
                        'provider/_providers.html.twig',
                        [
                            'providers' => $providers,
                            'mode' => $mode
                        ]
                    ),
                    'sorting' => $this->renderView(
                        'provider/_sorting.html.twig',
                        ['providers' => $providers]
                    ),
                    'pagination' => $this->renderView(
                        'provider/_pagination.html.twig',
                        ['providers' => $providers]
                    ),
                ]
            );
        }

        if ($request->get('ajax')) {
            return new JsonResponse(
                [
                    'content' => $this->renderView(
                        'provider/_providers.html.twig',
                        ['providers' => $providers]
                    ),
                    'sorting' => $this->renderView(
                        'provider/_sorting.html.twig',
                        ['providers' => $providers]
                    ),
                    'pagination' => $this->renderView(
                        'provider/_pagination.html.twig',
                        ['providers' => $providers]
                    ),
                ]
            );
        }

        return $this->render(
            'provider/index.html.twig',
            [
                'providers' => $providers,
                'form' => $form->createView(),
                'mode' => $mode,
                'documentId' => $documentId,
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'provider_delete', methods: ["POST"])]
    public function delete(Request $request, Provider $provider): Response
    {
        if ($this->isCsrfTokenValid(
            'delete' . $provider->getId(),
            $request->request->get('_token')
        )) {
            $this->manager->remove($provider);
            $this->manager->flush();
        }

        return $this->redirectToRoute(
            'provider_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}
