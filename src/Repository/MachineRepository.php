<?php

namespace App\Repository;

use App\Entity\Machine;
use App\Data\SearchMachine;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Id;

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
            ->setParameter('disabled', true)
        ;

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
                ->setParameter('constructor', "%{$constructor}%")
            ;
        }

        if (!empty($search->model)) {
            $model = strtoupper($search->model);
            $query = $query
                ->andWhere('m.model LIKE :model')
                ->setParameter('model', "%{$model}%")
            ;
        }

        if (!empty($search->workshop)) {
            $query = $query
                ->andWhere('w.id = :workshop')
                ->setParameter('workshop', $search->workshop)
            ;
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            20
        );
    }
}
