<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Data\SearchProvider;
use App\Form\SearchProviderForm;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\Str;

/**
 * @Route("/provider")
 */
class ProviderController extends AbstractController
{
    private $providerRepository;
    private $manager;

    public function __construct(ProviderRepository $providerRepository, EntityManagerInterface $manager)
    {
        $this->providerRepository = $providerRepository;
        $this->manager = $manager;
    }
    /**
     * Liste des fournisseurs
     * 
     * @Route("/{mode?}/{documentId?}", name="provider_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     * 
     * @param Request $request
     * @return Response 
     */
    public function index(Request $request, ?string $mode, ?int $documentId = null): Response
    {
        $data = new SearchProvider();
        $data->page = $request->get('page', 1);
        $data->organisation = $this->getUser()->getOrganisation()->getId();

        $form = $this->createForm(SearchProviderForm::class, $data);
        $form->handleRequest($request);
        
        $providers = $this->providerRepository->findSearch($data);

        if ($request->get('ajax') && ($mode == 'selectProvider')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('provider/_providers.html.twig', ['providers' => $providers, 'mode' => $mode]),
                'sorting'       =>  $this->renderView('provider/_sorting.html.twig', ['providers' => $providers]),
                'pagination'    =>  $this->renderView('provider/_pagination.html.twig', ['providers' => $providers]),
            ]);
        }

        if ($request->get('ajax')) {
            return new JsonResponse([
                'content'       =>  $this->renderView('provider/_providers.html.twig', ['providers' => $providers]),
                'sorting'       =>  $this->renderView('provider/_sorting.html.twig', ['providers' => $providers]),
                'pagination'    =>  $this->renderView('provider/_pagination.html.twig', ['providers' => $providers]),
            ]);
        }

        return $this->render('provider/index.html.twig', [
            'providers' =>  $providers,
            'form'  =>  $form->createView(),
            'mode' => $mode,
            'documentId' => $documentId,
        ]);
    }

    /**
     * @Route("/new", name="provider_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $provider = new Provider();
        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $provider->setCode(strtoupper($provider->getCode()));
            $this->Manager->persist($provider);
            $this->Manager->flush();

            return $this->redirectToRoute('provider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('provider/new.html.twig', [
            'provider' => $provider,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="provider_show", methods={"GET"})
     */
    public function show(Provider $provider): Response
    {
        return $this->render('provider/show.html.twig', [
            'provider' => $provider,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="provider_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Provider $provider): Response
    {
        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            return $this->redirectToRoute('provider_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('provider/edit.html.twig', [
            'provider' => $provider,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="provider_delete", methods={"POST"})
     */
    public function delete(Request $request, Provider $provider): Response
    {
        if ($this->isCsrfTokenValid('delete' . $provider->getId(), $request->request->get('_token'))) {
            $this->manager->remove($provider);
            $this->manager->flush();
        }

        return $this->redirectToRoute('provider_index', [], Response::HTTP_SEE_OTHER);
    }
}
