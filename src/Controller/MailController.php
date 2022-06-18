<?php

namespace App\Controller;

use DateInterval;
use App\Entity\Connexion;
use Symfony\Component\Mime\Address;
use App\Entity\Workorder;
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
    private $paramsRepository;
    private $workorderRepository;
    private $templateRepository;
    private $workorderStatusRepository;
    private $userRepository;
    private $manager;


    public function __construct(
        EntityManagerInterface $manager,
        TemplateRepository $templateRepository,
        WorkorderRepository $workorderRepository,
        ParamsRepository $paramsRepository,
        WorkorderStatusRepository $workorderStatusRepository,
        UserRepository $userRepository
    ) {
        $this->paramsRepository = $paramsRepository;
        $this->workorderRepository = $workorderRepository;
        $this->templateRepository = $templateRepository;
        $this->manager = $manager;
        $this->workorderStatusRepository = $workorderStatusRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/sendmail", name="send_mail")
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(MailerInterface $mailer): Response
    {        
        $email = (new TemplatedEmail())
            ->from(new Address('pierre.schmidt@gmaops.fr'))
            ->to('fr.libs@gmail.com')
            ->subject('Reset mot de passe')
            ->htmlTemplate('testemail/email.html.twig')
            ;

        $mailer->send($email);

        return $this->redirectToRoute('work_order_index'); 
    }
}
