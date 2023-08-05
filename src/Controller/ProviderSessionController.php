<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProviderSessionController extends AbstractController
{

    
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    #[Route('/provider/session/save/{providerId}', name: 'session_save_provider')]
    public function addprovider(int $providerId): Response
    {
        $this->requestStack->getSession()->set('providerId', $providerId);

        return $this->redirectToRoute('delivery_note_new');
    }
}
