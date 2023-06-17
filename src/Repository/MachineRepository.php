<?php

namespace App\Repository;

use App\Entity\Machine;
use App\Data\GlobalSearch;
use App\Data\SearchMachine;
use App\Entity\Organisation;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class MachineRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Machine::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les machines liées à une recherche
     *
     * @param SearchMachine $search
     * @return PaginationInterface
     */
    public function findSearch(SearchMachine $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('m')
            ->orderBy('m.constructor', 'ASC')
            ->select('m', 'w')
            ->join('m.workshop', 'w')
            ->andWhere('m.active = :disabled')
            ->setParameter('disabled', true);

        if (!empty($search->internalCode)) {
            $internalCode = strtoupper($search->internalCode);
            $query = $query
                ->andWhere('m.internalCode LIKE :internalCode')
                ->setParameter('internalCode', "%{$internalCode}%");
        }

        if (!empty($search->designation)) {
            $designation = strtoupper($search->designation);
            $query = $query
                ->andWhere('m.designation LIKE :designation')
                ->setParameter('designation', "%{$designation}%");
        }

        if (!empty($search->constructor)) {
            $constructor = strtoupper($search->constructor);
            $query = $query
                ->andWhere('m.constructor LIKE :constructor')
                ->setParameter('constructor', "%{$constructor}%");
        }

        if (!empty($search->model)) {
            $model = strtoupper($search->model);
            $query = $query
                ->andWhere('m.model LIKE :model')
                ->setParameter('model', "%{$model}%");
        }

        if (!empty($search->serialNumber)) {
            $serialNumber = strtoupper($search->serialNumber);
            $query = $query
                ->andWhere('m.serialNumber LIKE :serialNumber')
                ->setParameter('serialNumber', "%{$serialNumber}%");
        }

        if (!empty($search->workshop)) {
            $query = $query
                ->andWhere('w.id = :workshop')
                ->setParameter('workshop', $search->workshop);
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            20
        );
    }

    /**
     * Récupère les machines liées à une recherche d'un mot
     *
     * @param Sorganisation
     * @param $globalSearch
     * 
     * @return Machine[]
     */
    public function findGlobalSearch($organisation, GlobalSearch $globalSearch)
    {
        $word =  "%".strtoupper($globalSearch->search)."%";
    
        return $this->createQueryBuilder('m')
            ->select('m')
            ->andWhere('m.organisation = :organisation')

            ->andWhere('m.active = true')

            ->andWhere('
                m.designation LIKE :word 
                OR 
                m.constructor LIKE :word
                OR
                m.serialNumber LIKE :word
                OR
                m.internalCode LIKE :word
                OR
                m.model LIKE :word
            ')

            ->setParameters(new ArrayCollection([
                new Parameter('organisation', $organisation),
                new Parameter('word', $word),
            ])) 

            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

   
}