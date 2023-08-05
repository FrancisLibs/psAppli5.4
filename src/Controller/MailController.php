<?php

namespace App\Controller;

use Symfony\Component\Mime\Address;
use App\Repository\UserRepository;
use App\Repository\ParamsRepository;
use App\Repository\TemplateRepository;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\WorkorderStatusRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailController extends AbstractController
{
    /**
     * @Route("/sendmail",                  name="send_mail")
     * @Security("is_granted('ROLE_USER')")
     */
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
