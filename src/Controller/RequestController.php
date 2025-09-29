<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Repository\PartRepository;
use App\Repository\ProviderRepository;
use App\Service\OrganisationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class RequestController extends AbstractController
{
    protected PartRepository $partRepository;
    protected ProviderRepository $providerRepository;
    protected OrganisationService $organisation;
    protected MailerInterface $mailer;
    protected RequestStack $requestStack;
    protected Security $security;

    public function __construct(
        OrganisationService $organisation,
        PartRepository $partRepository,
        ProviderRepository $providerRepository,
        MailerInterface $mailer,
        RequestStack $requestStack,
        Security $security
    ) {
        $this->organisation = $organisation;
        $this->partRepository = $partRepository;
        $this->providerRepository = $providerRepository;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    #[Route('/request/{id}', name: 'app_request')]
    public function index(Provider $provider): Response
    {
        $organisation = $this->organisation->getOrganisation();
        $parts = $this->partRepository->findProviderParts($organisation, $provider);

        $startMessage = "Bonjour,\n\nMerci de me faire une offre pour les matériels ci-dessous :";
        $endMessage = "Cordialement";

        return $this->render('request/index.html.twig', [
            'startMessage' => nl2br($startMessage),
            'endMessage' => nl2br($endMessage),
            'provider' => $provider,
            'parts' => $parts,
        ]);
    }

    #[Route('/parts-selection', name: 'quotation-parts-select', methods: ['POST'])]
    public function traiterSelection(Request $request): Response
    {
        $user = $this->security->getUser();
        $providerName = $request->request->get('provider_name');
        $providerId = $request->request->get('provider_id');
        $providerEmail = $request->request->get('provider_email');
        $selectedPartIds = $request->request->get('selected_parts');
        $quantities = $request->request->get('quantities');
        $startMessage = $request->request->get('startMessage');
        $endMessage = $request->request->get('endMessage');

        $providers = [];
        for ($i = 0; $i < count($providerName); $i++) {
            if ($providerEmail[$i] !== "") {
                $providers[] = [
                    'providerName' => $providerName[$i],
                    'providerId' => $providerId[$i],
                    'providerEmail' => $providerEmail[$i],
                ];
            }
        }

        if (empty($selectedPartIds)) {
            $this->addFlash('error', 'Attention tu n\'as selectionné aucun article');
            return $this->redirectToRoute('app_request', ['id' => $providerId[0]]);
        }

        $parts = [];
        foreach ($quantities as $partId => $quantity) {
            if (in_array($partId, $selectedPartIds)) {
                $part = $this->partRepository->find($partId);
                if ($part) {
                    $parts[] = ['part' => $part, 'quantities' => $quantity];
                }
            }
        }

        foreach ($providers as $provider) {
            $emailContent = $this->renderView('request/request_mail.html.twig', [
                'startMessage' => $startMessage,
                'endMessage' => $endMessage,
                'parts' => $parts,
                'provider' => $provider,
            ]);

            $email = (new \Symfony\Component\Mime\Email())
                ->from('pierre.schmidt@gmaops.fr')
                ->to($provider['providerEmail'])
                ->addTo($user->getEmail())
                ->subject('Pièces à chiffrer')
                ->html($emailContent);

            $this->mailer->send($email);
        }

        return $this->render('request/result.html.twig', [
            'user' => $user,
            'providers' => $providers,
            'parts' => $parts,
        ]);
    }
}
