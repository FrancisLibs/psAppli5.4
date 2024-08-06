<?php

namespace App\Controller;

use App\Entity\Provider;
use Symfony\Component\Mime\Email;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use App\Repository\ProviderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class RequestController extends AbstractController
{

    protected $partRepository;

    protected $organisation;

    protected $providerRepository;

    protected $session;

    protected $mailer;

    protected $requestStack;

    protected $security;

    
    public function __construct(
        SessionInterface $sessionInterface,
        OrganisationService $organisation,
        PartRepository $partRepository,
        ProviderRepository $providerRepository,
        MailerInterface $mailer,
        RequestStack $requestStack,
        Security $security,
    ) {
        $this->organisation = $organisation;
        $this->partRepository = $partRepository;
        $this->providerRepository = $providerRepository;
        $this->session = $sessionInterface;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    /**
     * Affiche la page de demande pour un fournisseur 
     * spécifique et prépare les messages de début et de fin.
     *
     * @param  Provider $provider Le fournisseur pour lequel la demande est faite.
     * @return Response La réponse HTTP avec le rendu de la vue.
     */
    #[Route('/request/{id}', name: 'app_request')]
    public function index(Provider $provider): Response
    {
        $organisation = $this->organisation->getOrganisation();

        $parts = $this->partRepository->findProviderParts($organisation, $provider);
        $startMessage
            = "Bonjour, 
            \n\nMerci de me faire une offre pour les matériels ci-dessous :";
        $startMessageBR = nl2br($startMessage);
        $endMessage = "Cordialement";
        $endMessageBR = nl2br($endMessage);

        return $this->render(
            'request/index.html.twig',
            [
            'startMessage' => $startMessageBR,
            'endMessage'  => $endMessageBR,
            'provider' => $provider,
            'parts' => $parts,
            ]
        );
    }

    /**
    * Traite la sélection des pièces et envoie des emails de demande de prix aux 
    * fournisseurs sélectionnés.
    *
    * @param  Request $request La requête HTTP contenant les données du formulaire.
    * @return Response
    */
    #[Route('/parts-selection', name: 'quotation-parts-select', methods: ['POST'])]
    public function traiterSelection(Request $request): Response
    {
        $user = $this->getUser();

        $providerName = $request->request->get('provider_name');
        $providerId = $request->request->get('provider_id');
        $providerEmail = $request->request->get('provider_email');
        $selectedPartIds = $request->request->get('selected_parts');
        $quantities = $request->request->get('quantities');
        $startMessage = $request->request->get('startMessage');
        $endMessage = $request->request->get('endMessage');
        $copy = $request->request->get('copyOfMail');

        $providers = [];

        // Creation of an provider array
        for ($index = 0; $index < count($providerName); $index++) {
            $providers[$index] = [
                    'providerName' => $providerName[$index],
                    'providerId' => $providerId[$index],
                    'providerEmail' => $providerEmail[$index]
                ];
            
        }
        
        // Removing providers without email
        $index = 0;
        foreach ($providers as $provider) {
            if ($provider['providerEmail'] == "") {
                array_splice($providers, $index, 1);
            }
            $index++;
        }

        // If are no parts then setting an error message
        if (empty($selectedPartIds)) {
            $this->addFlash('error', 'Attention tu n\'as selectionné aucun article');
            return
                $this->redirectToRoute('app_request', ['id' => $provider->getId()]);
        }

        // Creation of an part array
        $parts = [];
        foreach ($quantities as $partId => $quantity) {
            if (in_array($partId, $selectedPartIds)) {
                $part = $this->partRepository->findOneById($partId);
                if ($part) {
                    $parts[] = [
                        'part' => $part,
                        'quantities' => $quantity,
                    ];
                }
            }
        }
        
        // Construction du contenu de l'e-mail en utilisant le rendu de template 
        // pour chaque fournisseur
        foreach ($providers as $provider) {
            $emailContent = $this->renderView(
                'request/request_mail.html.twig',
                [
                    'startMessage' => $startMessage,
                    'endMessage' => $endMessage,
                    'parts' => $parts,
                    'provider' => $provider,
                ]
            );

            // Envoi de l'e-mail
            $email = (new Email())
                ->from('pierre.schmidt@gmaops.fr')
                ->to($provider['providerEmail'])
                ->addTo($user->getEmail())
                ->subject('Pièces à chiffrer')
                ->html($emailContent);

            $this->mailer->send($email);

        }

        return $this->render(
            'request/result.html.twig',
            [
                'user' => $user,
                'providers' => $providers,
                'parts' => $parts
            ]
        );
    }
}
