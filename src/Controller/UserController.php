<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use App\Service\OrganisationService;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as extraSecurity;

class UserController extends AbstractController
{
    private $hasher;
    private $security;
    private $userRepository;
    private $workorderRepository;
    private $manager;
    private $organisation;

    public function __construct(
        EntityManagerInterface $manager,
        Security $security,
        UserRepository $userRepository,
        WorkorderRepository $workorderRepository,
        UserPasswordHasherInterface $passwordHasher,
        OrganisationService $organisation,
    ) {
        $this->hasher = $passwordHasher;
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->workorderRepository = $workorderRepository;
        $this->manager = $manager;
        $this->organisation = $organisation;
    }

    /**
     * Users list
     * 
     * @Route("/admin/user/index", name="user_index")
     * @extraSecurity("is_granted('ROLE_ADMIN')")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function userList()
    {
        $user = $this->getUser();
        $organisation = $this->organisation->getOrganisation();
        $users = $this->userRepository->findAllActive();

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
    public function userEdit(Request $request, User $user): Response
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
        return $this->render('user/edit.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
        ]);
    }

    /**
     * Delete user
     *
     * @Route("/user/{id}/delete", name="user_delete", methods={"POST"} )
     * @extraSecurity("is_granted('ROLE_ADMIN')")
     * @param                      User $user
     * @return                     RedirectResponse
     */
    public function userDelete(Request $request, User $user)
    {
        $token = $request->request->get('_token');
        $currentUser = $this->getUser();

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $token)) {
            if ($user <> $currentUser) {
                $user->setActive(false);
                $user->setRoles([]);
                $this->manager->flush();
                $this->addFlash('success', 'L\'utilisateur a bien été désactivé.');
                return $this->redirectToRoute('user_index');
            }

            if ($user == $currentUser) {
                $this->addFlash('error', 'Vous ne pouvez pas vous désactivé vous-même');
                return $this->redirectToRoute('user_index');
            }
        }

        $this->addFlash('error', 'L\'utilisateur n\'a pas été désactivé.');
        return $this->redirectToRoute('user_index');
    }

    /**
     * User profil
     *
     * @Route("/user/{id}/profil", name="user_profil")
     * @extraSecurity("is_granted('ROLE_USER')")
     * @param                      User $user
     * @return                     RedirectResponse
     */
    public function userProfil(User $user)
    {
        // BT de l'utilisateur
        $workorders = $this->workorderRepository->findBy(['user' => $user]);
        

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'workorder' => $workorders
        ]);
    }
}
