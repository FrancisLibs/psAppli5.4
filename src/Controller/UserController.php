<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Token;
use App\Data\SearchUser;
use App\Form\UserEditType;
use App\Form\SearchUserForm;
use App\Form\UserPasswordType;
use App\Form\UserInscriptionType;
use App\Repository\UserRepository;
use App\Repository\IdentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as extraSecurity;

class UserController extends AbstractController
{
    private $hasher;
    private $security;
    private $userRepository;
    private $manager;
   
    public function __construct(EntityManagerInterface $manager, 
        Security $security, 
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher)
    {
        $this->hasher = $passwordHasher;
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    /**
     * Users list
     * 
     * @Route("/admin/user/index", name="user_list")
     * @extraSecurity("is_granted('ROLE_ADMIN')")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function userList(Request $request)
    {    
        $data = new SearchUser();
        $data->page = $request->get('page', 1);
        $form = $this->createForm(SearchUserForm::class, $data);
        $form->handleRequest($request);
        $users = $this->userRepository->findSearch($data);
        if($request->get('ajax')){
            return new JsonResponse([
                'content'       =>  $this->renderView('user/_users.html.twig', ['users' => $users]),
                'sorting'       =>  $this->renderView('user/_sorting.html.twig', ['users' => $users]),
                'pagination'    =>  $this->renderView('user/_pagination.html.twig', ['users' => $users]),
            ]);
        }
        return $this->render('user/list.html.twig', [
            'users' => $users,
            'form'  => $form->createView(),
        ]);
    }
    
    /**
     * Create user
     * 
     * @Route("/user/create", name="user_new")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function userCreate(Request $request, \Swift_Mailer $mailer,TokenGeneratorInterface $tokenGenerator)
    {       
        $user = new User();
        $form = $this->createForm(UserInscriptionType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) 
        {          
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setActive(false);
            $this->manager->persist($user);
            $this->manager->flush();

            $token = (new Token());
            $token->setToken($tokenGenerator->generateToken())
                ->setCreatedAt(new \DateTime()); 
            $this->manager->persist($token);
            $this->manager->flush();

            $url = "";

            $message = (new \Swift_Message('Hello Email'))
            ->setFrom('fr.libs@gmail.com')
            ->setTo('fr.libs@gmail.com')
            ->setBody(
                $this->renderView(
                    'emails/registration.html.twig',
                    ['name' => $user->getFirstName()]
                ),
                'text/html'
            );
            $mailer->send($message);

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            if($this->security->isGranted('ROLE_ADMIN')) 
            {
                return $this->redirectToRoute('user_list');
            }
            return $this->redirectToRoute('home');
        }
        return $this->render('user/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit user
     * 
     * @Route("/user/{id}/edit", name="user_edit")
     * @extraSecurity("is_granted('ROLE_USER')")
     * @param  User $user
     * @param  Request $request
     * @param  EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function userEdit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {         
             
            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié.");
            return $this->redirectToRoute('user_list');
        }
        return $this->render('user/modify.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
        ]);
    }
    
    /**
     * Delete user
     *
     * @Route("/user/{id}/delete", name="user_delete", methods="DELETE")
     * @extraSecurity("is_granted('ROLE_ADMIN')")
     * @param                      User $user
     * @return                     RedirectResponse
     */
    public function userDelete(User $user, Request $request)
    {
        $token = $request->request->get('token');
        $currentUser = $this->getUser();
        
        if ($this->isCsrfTokenValid('delete', $token)) {
            if ($user <> $currentUser) {
                $this->manager->remove($user);
                $this->manager->flush();
                $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');
                return $this->redirectToRoute('user_list');
            }

            if ($user == $currentUser) {
                $this->addFlash('error', 'Vous ne pouvez pas vous supprimer vous-même');
                return $this->redirectToRoute('user_list');
            }
        }

        $this->addFlash('error', 'L\'utilisateur n\'a pas été supprimé.');
        return $this->redirectToRoute('user_list');
    }

    /**
     * User profil
     *
     * @Route("/user/{id}/profil", name="user_profil")
     * @extraSecurity("is_granted('ROLE_USER')")
     * @param                      User $user
     * @return                     RedirectResponse
     */
    public function userProfil(User $user, Request $request)
    {
        return $this->render('user/profil.html.twig', [
            'user' => $user
        ]);
    }

     /**
     * User profil
     *
     * @Route("/user/{id}/changePassword", name="user_password_modification")
     * @param                      User $user
     * @return                     RedirectResponse
     */
    public function userChangePassword(User $user, Request $request)
    {
        $form = $this->createForm(UserPasswordType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) 
        {          
            $user->setPassword($this->encoder->encodePassword($user, 'password'));
            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', "Le mot de passe a bien été changé.");

            return $this->redirectToRoute('user_profil');
        }
        
        return $this->render('user/passwordChange.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
        ]);
    }

}