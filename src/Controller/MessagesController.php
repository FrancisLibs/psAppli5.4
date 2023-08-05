<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Form\MessagesType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Message;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagesController extends AbstractController
{
    private $_userRepository;
    private $_manager;
    private $_organisation;


    public function __construct(
        OrganisationService $organisation, 
        UserRepository $userRepository, 
        EntityManagerInterface $manager
    ) {
        $this->_userRepository = $userRepository;
        $this->_manager = $manager;
        $this->_organisation = $organisation;
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/messages', name: 'messages')]
    public function index(): Response
    {
        return $this->render(
            'messages/index.html.twig', 
            ['controller_name' => 'MessagesController']
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/send', name: 'send')]
    public function send(Request $request): Response
    {
        $message = new Messages;
        $form = $this->createForm(MessagesType::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $message->setSender($user);

            if ($form->get('all')->getData()) {
                $organisation = $this->_organisation->getOrganisation();
                $service = $user->getService();
                $receivers = $this->_userRepository->findBy(
                    [
                    'organisation' => $organisation,
                    'service' => $service,
                    ]
                );

                foreach ($receivers as $receiver) {
                    if ($receiver <> $user) {
                        $newMessage = new Messages;
                        $newMessage->setRecipient($receiver)
                            ->setCreatedAt(new \DateTime())
                            ->setIsRead(false)
                            ->setMessage($message->getMessage())
                            ->setSender($user)
                            ->setTitle($message->getTitle());

                        $this->_manager->persist($newMessage);
                    }
                }
                $this->addFlash(
                    'success', 
                    "Ton message a bien été envoyé à tout les membres du service"
                );
            } else {
                $this->_manager->persist($message);
                $this->addFlash(
                    'success', 
                    "Ton message a bien été envoyé à " . $message->getRecipient()->getFirstName()
                );
            }
            $this->_manager->flush();

            return $this->redirectToRoute("messages");
        }

        return $this->render(
            "messages/send.html.twig", [
            "form" => $form->createView()
            ]
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/received', name: 'received')]
    public function received(): Response
    {
        return $this->render('messages/received.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/read/{id}', name: 'read')]
    public function read(Messages $message): Response
    {
        $message->setIsRead(true);

        $this->_manager->persist($message);
        $this->_manager->flush();

        return $this->render(
            'messages/read.html.twig',
            compact("message")
        );
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Messages $message): Response
    {
        $this->_manager->remove($message);
        $this->_manager->flush();

        return $this->redirectToRoute("received");
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/sent', name: 'sent')]
    public function sent(): Response
    {
        return $this->render('messages/sent.html.twig');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/respond/{id}', name: 'respond')]
    public function respond(Request $request, Messages $messages): Response
    {
        $messagesNew = new Messages;
        $messagesNew->setRecipient($messages->getSender());
        $form = $this->createForm(MessagesType::class, $messagesNew);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $messagesNew->setSender($user);

            $this->_manager->persist($messagesNew);
            $this->addFlash('success', "Ta réponse a bien été envoyée.");
            $this->_manager->flush();
            return $this->redirectToRoute("messages");
        }

        return $this->render(
            "messages/send.html.twig", [
            "form" => $form->createView()
            ]
        );
    }
}
