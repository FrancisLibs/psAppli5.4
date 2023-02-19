<?php

namespace App\Controller;

use App\Entity\Machine;
use App\Form\MachineType;
use App\Repository\MachineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChildMachineController extends AbstractController
{

    #[Route('/child/machine/add/{parentId}', name: 'child_machine_add')]
    public function new(Request $request, MachineRepository $machineRepository, EntityManagerInterface $manager, $parentId): Response
    {
        $parent = $machineRepository->find($parentId);

        if ($parent->getChildLevel()===null) {
            $childMachine = new Machine();

            $form = $this->createForm(MachineType::class, $childMachine);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $childMachine->setCreatedAt((new \Datetime()));
                $childMachine->setStatus(true);
                $childMachine->setInternalCode(strtoupper($childMachine->getInternalCode()));
                $childMachine->setConstructor(strtoupper($childMachine->getConstructor()));
                $childMachine->setDesignation(mb_strtoupper($childMachine->getDesignation()));
                $childMachine->setActive(true);
                $childMachine->setParent($parent);
                $childMachine->setChildLevel(1);
                $manager->persist($childMachine);
                $manager->flush();

                return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
            }
            return $this->renderForm('machine/new.html.twig', [
                'machine' => $childMachine,
                'form' => $form,
            ]);
        } 
        
        $this->get('session')->getFlashBag()->set('error', 'Une machine ne peut avoir qu\'un seul sous-niveau');

            return $this->redirectToRoute('machine_index', [], Response::HTTP_SEE_OTHER);
        }
    }
}
