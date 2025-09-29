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
            ->select('o, p, org, a')
            ->join('o.organisation', 'org')
            ->join('o.provider', 'p')
            ->join('o.accountType', 'a')
            ->join('o.createdBy', 'u');
            

        if (!empty($search->organisation)) {
            $query = $query
                ->andWhere('o.organisation = :organisation')
                ->setParameter('organisation', $search->organisation);
        }

        if (!empty($search->designation)) {
            $designation = strtoupper($search->designation);
            $query = $query
                ->andWhere('o.designation LIKE :designation')
                ->setParameter('designation', "%{$designation}%");
        }

        if (!empty($search->accountType)) {
            dd($search->accountType);
            $query = $query
                ->andWhere('o.accountType = :accountType')
                ->setParameter('accountType', $search->accountType);
        }

        // if (!empty($search->number)) {
        //     $query = $query
        //         ->andWhere('p.designation LIKE :designation')
        //         ->setParameter('designation', "%{$designation}%");
        // }

        // if (!empty($search->provider)) {
        //     $query = $query
        //         ->andWhere('p.designation LIKE :designation')
        //         ->setParameter('designation', "%{$designation}%");
        // }



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
