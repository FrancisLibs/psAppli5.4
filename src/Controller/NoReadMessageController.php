<?php

namespace App\Controller;

use App\Repository\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NoReadMessageController extends AbstractController
{
    #[Route('/no/read/message', name: 'isNotRead')]
    public function noread(MessagesRepository $messagesRepository): Response
    {
        $user = $this->getUser();
        $noReadMails = $messagesRepository->countNoReadMessages($user);
        
        return $this->render('no_read_message/mailCount.html.twig', [
            'noReadMails' => $noReadMails,
        ]);
    }
}
