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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class GlobalSearchController extends AbstractController
{
    private $machineRepository;
    private $partRepository;
    private $deliveryNoteRepository;
    private $providerRepository;
    private $workorderRepository;
    private $organisation;

    public function __construct(
        MachineRepository $machineRepository, 
        PartRepository $partRepository, 
        DeliveryNoteRepository $deliveryNoteRepository, 
        ProviderRepository $providerRepository,
        WorkorderRepository $workorderRepository,
        OrganisationService $organisation,
    )
        {
            $this->machineRepository = $machineRepository;
            $this->partRepository = $partRepository;
            $this->deliveryNoteRepository = $deliveryNoteRepository;
            $this->partRepository = $partRepository;
            $this->providerRepository = $providerRepository;
            $this->workorderRepository = $workorderRepository;
            $this->organisation = $organisation;
        }
        
    #[IsGranted('ROLE_USER')]
    #[Route('/global/search', name: 'app_global_search')]
    public function index(Request $request )
    {
        $organisation =  $this->organisation->getOrganisation();

        $data = new GlobalSearch();

        $form = $this->createForm(GlobalSearchType::class, $data);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $machines = $this->machineRepository->findGlobalSearch($organisation, $data);
            $parts = $this->partRepository->findGlobalSearch($organisation, $data);
            $deliveryNotes = $this->deliveryNoteRepository->findGlobalSearch($organisation, $data);
            $providers = $this->providerRepository->findGlobalSearch($organisation, $data);
            $workorders = $this->workorderRepository->findGlobalSearch($organisation, $data);

            return $this->render('global_search/resultDisplay.html.twig', [
                'machines'      => $machines,
                'parts'         =>  $parts,
                'deliveryNotes' => $deliveryNotes,
                'providers'     => $providers,
                'workorders'    =>  $workorders,
            ]);
        }

        return $this->render('global_search/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
