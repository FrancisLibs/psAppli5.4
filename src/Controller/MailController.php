<?php

namespace App\Controller;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route(
        '/sendmail', 
        name: 'send_mail', 
    )]
    public function index(MailerInterface $mailer): Response
    {        
        $email = (new TemplatedEmail())
            ->from(new Address('pierre.schmidt@gmaops.fr'))
            ->to('fr.libs@gmail.com')
            ->subject('Reset mot de passe')
            ->htmlTemplate('testemail/email.html.twig');

        $mailer->send($email);

        return $this->redirectToRoute('work_order_index'); 
    }
}
