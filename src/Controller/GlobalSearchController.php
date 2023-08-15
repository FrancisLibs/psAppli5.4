<?php

namespace App\Controller;

use App\Data\GlobalSearch;
use App\Form\GlobalSearchType;
use App\Repository\PartRepository;
use App\Service\OrganisationService;
use App\Repository\MachineRepository;
use App\Repository\ProviderRepository;
use App\Repository\WorkorderRepository;
use App\Repository\DeliveryNoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class GlobalSearchController extends AbstractController
{
    private $_machineRepository;
    private $_partRepository;
    private $_deliveryNoteRepository;
    private $_providerRepository;
    private $_workorderRepository;
    private $_organisation;


    public function __construct(
        MachineRepository $machineRepository,
        PartRepository $partRepository,
        DeliveryNoteRepository $deliveryNoteRepository,
        ProviderRepository $providerRepository,
        WorkorderRepository $workorderRepository,
        OrganisationService $organisation,
    ) {
        $this->_machineRepository = $machineRepository;
        $this->_partRepository = $partRepository;
        $this->_deliveryNoteRepository = $deliveryNoteRepository;
        $this->_partRepository = $partRepository;
        $this->_providerRepository = $providerRepository;
        $this->_workorderRepository = $workorderRepository;
        $this->_organisation = $organisation;
    }

    
    #[IsGranted('ROLE_USER')]
    #[Route('/global/search', name: 'app_global_search')]
    public function index(Request $request)
    {
        $organisation = $this->_organisation->getOrganisation();

        $data = new GlobalSearch();

        $form = $this->createForm(GlobalSearchType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $machines = $this->_machineRepository->findGlobalSearch(
                $organisation, 
                $data
            );
            $parts = $this->_partRepository->findGlobalSearch(
                $organisation, 
                $data
            );
            $deliveryNotes = $this->_deliveryNoteRepository->findGlobalSearch(
                $organisation, 
                $data
            );
            $providers = $this->_providerRepository->findGlobalSearch(
                $organisation, 
                $data
            );
            $workorders = $this->_workorderRepository->findGlobalSearch(
                $organisation, 
                $data
            );

            return $this->render(
                'global_search/resultDisplay.html.twig', 
                [
                'machines'      => $machines,
                'parts'         =>  $parts,
                'deliveryNotes' => $deliveryNotes,
                'providers'     => $providers,
                'workorders'    =>  $workorders,
                ]
            );
        }

        return $this->render(
            'global_search/form.html.twig', [
            'form' => $form->createView()
            ]
        );
    }
}
