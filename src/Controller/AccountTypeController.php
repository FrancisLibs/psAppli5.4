<?php

namespace App\Controller;

use App\Entity\AccountType;
use App\Form\AccountTypeType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AccountTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/accountType")
 */

class AccountTypeController extends AbstractController
{
    protected $accountTypeRepository;
    protected $manager;

    public function __construct(
        AccountTypeRepository $accountTypeRepository, 
        EntityManagerInterface $manager,
    ) {
        $this->accountTypeRepository = $accountTypeRepository;
        $this->manager = $manager;
    }       
    
    /**
     * AccountType list
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/index', name: 'accountType_index')]
    public function accountTypeList(): Response
    {
        $accountTypes = $this->accountTypeRepository->findAll();
        return $this->render(
            'account_type/index.html.twig', [
            'accountTypes' => $accountTypes,
            ]
        );
    }

    /**
     * AccountType new
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'accountType_new', methods: ["GET", "POST"])]
    public function new(Request $request,): Response
    {
        $accountType = new AccountType();
        $form = $this->createForm(AccountTypeType::class, $accountType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountType->setLetter(strtoupper($accountType->getLetter()));
            $this->manager->persist($accountType);
            $this->manager->flush();

            return $this->redirectToRoute('accountType_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'account_type/new.html.twig', [
            'form' => $form->createView(),
            'accountType' => $accountType,
            ]
        );
    }

    /**
    * @ Edition type de compte
    */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'accountType_edit', methods: ["GET", "POST"])]
    public function edit(Request $request, AccountType $accountType): Response
    {
        $form = $this->createForm(AccountTypeType::class, $accountType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountType->setLetter(strtoupper($accountType->getLetter()));
            $this->manager->flush();
       
            return $this->redirectToRoute('accountType_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render(
            'account_type/new.html.twig', [
            'form' => $form->createView(),
            'accountType' => $accountType,
            ]
        );
    }

    /**
     * @ Effacer type de compte
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'accountType_delete', methods: ["POST"])]
    public function delete(Request $request, AccountType $accountType): Response
    {
        if ($this->isCsrfTokenValid(
            'delete' . $accountType->getId(),
            $request->request->get('_token')
        )
        ) {
            $this->manager->remove($accountType);
            $this->manager->flush();
        }

        return $this->redirectToRoute('accountType_index', [], Response::HTTP_SEE_OTHER);
    }
}
