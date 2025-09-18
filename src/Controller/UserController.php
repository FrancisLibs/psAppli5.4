<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use App\Service\OrganisationService;
use App\Repository\WorkorderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as extraSecurity;

class UserController extends AbstractController
{
    protected $userRepository;
    protected $workorderRepository;
    protected $manager;
    protected $organisation;


    public function __construct(
        EntityManagerInterface $manager,
        UserRepository $userRepository,
        WorkorderRepository $workorderRepository,
        OrganisationService $organisation,
    ) {
        $this->userRepository = $userRepository;
        $this->workorderRepository = $workorderRepository;
        $this->manager = $manager;
        $this->organisation = $organisation;
    }


   
 /**
     * Users list
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user/index', name: 'user_index')]
    public function userList()
    {
        $users = $this->userRepository->findAllActive();

        return $this->render(
            'user/index.html.twig', [
            'users' => $users,
            ]
        );
    }
    /**
     * Edit user
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/user/{id}/edit', name: 'user_edit')]
    public function userEdit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié.");
            return $this->redirectToRoute(
                'user_profil', [
                'id' => $user->getId(),
                ]
            );
        }
        return $this->render(
            'user/edit.html.twig', [
            'form'  => $form->createView(),
            'user'  => $user,
            ]
        );
    }

    /**
     * Delete user
     */
    #[Route('/user/{id}/delete', name: 'user_delete', methods:["POST"])]
    #[IsGranted('ROLE_ADMIN')]
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
                $this->addFlash(
                    'error', 
                    'Vous ne pouvez pas vous désactivé vous-même'
                );
                return $this->redirectToRoute('user_index');
            }
        }

        $this->addFlash('error', 'L\'utilisateur n\'a pas été désactivé.');
        return $this->redirectToRoute('user_index');
    }

    /**
     * User profil
     */
    #[Route('/user/{id}/profil', name: 'user_profil')]
    #[IsGranted('ROLE_USER')]
    public function userProfil(User $user)
    {
        // BT de l'utilisateur
        $workorders = $this->workorderRepository->findBy(['user' => $user]);
        
        return $this->render(
            'user/profil.html.twig', [
            'user' => $user,
            'workorder' => $workorders
            ]
        );
    }
}
