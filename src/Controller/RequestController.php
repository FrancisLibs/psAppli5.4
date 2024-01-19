<?php

namespace App\Controller;

use App\Entity\Provider;
use Symfony\Component\Mime\Email;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use App\Repository\ProviderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RequestController extends AbstractController
{
    protected $partRepository;
    protected $organisation;
    protected $providerRepository;
    protected $session;
    protected $mailer;
    protected $requestStack;


    public function __construct(
        SessionInterface $sessionInterface,
        OrganisationService $organisation,
        PartRepository $partRepository,
        ProviderRepository $providerRepository,
        MailerInterface $mailer,
        RequestStack $requestStack,
    ) {
        $this->organisation = $organisation;
        $this->partRepository = $partRepository;
        $this->providerRepository = $providerRepository;
        $this->session = $sessionInterface;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
    }


    #[Route('/request/{id}', name: 'app_request')]
    public function index(Provider $provider): Response
    {
        $organisation = $this->organisation->getOrganisation();

        if (empty($provider->getEmail())) {
            $this->requestStack->getSession()->clear(); // Effacement d'un éventuel message
            $this->addFlash('error', 'Attention ce fournisseur n\'a pas d\'adresse email');
        }

        $parts = $this->partRepository->findProviderParts($organisation, $provider);
        //dd($parts);
        return $this->render(
            'request/index.html.twig',
            [
                'startMessage' => 'Bonjour, \n merci de me faire une offre pour les matériels ci-dessous :',
                'endMessage'  => 'Cordialement.',
                'provider' => $provider,
                'parts' => $parts,
            ]
        );
    }

    /**
     * @Route("/parts-selection", name="quotation-parts-select", methods={"POST"})
     */
    public function traiterSelection(Request $request): Response
    {
        $providerId = $request->request->get('provider_id');
        $selectedPartIds = $request->request->get('selected_parts');
        $quantities = $request->request->get('quantities');
        $startMessage = $request->request->get('startMessage');
        $endMessage = $request->request->get('endMessage');

        $provider = $this->providerRepository->findOneById($providerId);
        $email = $provider->getEmail();
        // dump($selectedPartIds);
        $this->requestStack->getSession()->clear(); // Effacement d'un éventuel message
        
        // if (empty($selectedPartIds)) {
        //     $this->addFlash('error', 'Attention tu n\'as selectionné aucun article');
        //     return $this->redirectToRoute('app_request', ['id' => $provider->getId()]);
        // }

        // if (empty($email)) {
        //     $this->addFlash('error', 'Attention ce fournisseur n\'a pas d\'adresse email');
        //     return $this->redirectToRoute('app_request', ['id' => $provider->getId()]);
        // }

        $parts = [];
        foreach ($quantities as $partId => $quantity) {
            if(in_array($partId, $selectedPartIds )) {
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
        $emailContent = $this->renderView(
            'request/request_mail.html.twig',
            [
                'startMessage' => $startMessage,
                'endMessage' => $endMessage,
                'parts' => $parts,
            ]
        );

        // Envoi de l'e-mail
        $email = (new Email())
            ->from('votre@email.com')
            ->to('fr.libs@gmail.com')
            ->subject('Pièces sélectionnées')
            ->html($emailContent);

        $this->mailer->send($email);

        return $this->render(
            'request/result.html.twig'
        );
    }
}
