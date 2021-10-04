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

    public function __construct(
        EntityManagerInterface $manager,
        Security $security,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->hasher = $passwordHasher;
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    /**
     * Users list
     * 
     * @Route("/admin/user/index", name="user_index")
     * @extraSecurity("is_granted('ROLE_ADMIN')")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function userList(Request $request)
    {
        $users = $this->userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Edit user
     * 
     * @Route("/user/{id}/edit", name="user_edit")
     * @param  User $user
     * @param  Request $request
     * @param  EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function userEdit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié.");
            return $this->redirectToRoute('user_profil', [
                'id' => $user->getId(),
            ]);
        }
        return $this->render('user/modify.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
        ]);
    }

    /**
     * Delete user
     *
     * @Route("/user/{id}/remove", name="user_remove", methods={"DELETE"})
     * @extraSecurity("is_granted('ROLE_ADMIN')")
     * @param                      User $user
     * @return                     RedirectResponse
     */
    public function userDelete(User $user, Request $request)
    {
        $token = $request->request->get('_token');
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
}
