<?php

namespace App\Repository;

use App\Entity\Order;
use App\Data\SearchOrder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class OrderRepository extends ServiceEntityRepository
{
    protected $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Order::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les pièces liées à une recherche
     *
     * @param  SearchOrder $search
     * @return PaginationInterface
     */
    public function findSearch(SearchOrder $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('o')
            ->leftjoin('o.organisation', 'org')->addSelect('org')
            ->leftjoin('o.provider', 'p')->addSelect('p')
            ->leftjoin('o.createdBy', 'u')->addSelect('u');
            
        if (!empty($search->organisation)) {
            $query = $query
                ->andWhere('org.id = :organisation')
                ->setParameter('organisation', $search->organisation);
        }

        if (!empty($search->designation)) {
            $query = $query
                ->andWhere('UPPER(o.designation) LIKE :designation')
                ->setParameter('designation', '%'.strtoupper($search->designation).'%');
        }
        if (!empty($search->number)) {
            $query = $query
                ->andWhere('o.number = :number')
                ->setParameter('number', $search->number);
        }

        if (!empty($search->provider)) {
            $query = $query
                ->andWhere('p.id = :provider')
                ->setParameter('provider', $search->provider);
        }

        if (!empty($search->date)) {
            $query = $query
                ->andWhere('o.date = :date')
                ->setParameter('date', $search->date);
        }

        if (!empty($search->createdBy)) {
            $query = $query
                ->andWhere('u.id = :createdBy')
                ->setParameter('createdBy', $search->createdBy);
        }



        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            20
        );
    }

    /**
     * Retourne le prochain numéro d'ordre dispo
     */
    public function getNextNumber(): int
    {
        $lastOrder = $this->findOneBy([], ['id' => 'DESC']);

        return ($lastOrder?->getNumber() ?? 0) + 1;
    }
}
