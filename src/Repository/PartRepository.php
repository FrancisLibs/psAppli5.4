<?php

namespace App\Repository;

use App\Entity\Part;
use App\Data\SearchPart;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class PartRepository extends ServiceEntityRepository
{

    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Part::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les commandes liées à une recherche
     *
     * @param SearchPart $search
     * @return PaginationInterface
     */
    public function findSearch(SearchPart $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.code', 'ASC')
            ->select('p', 's')
            ->join('p.stock', 's');

        if (!empty($search->code)) {
            $query = $query
                ->andWhere('p.code LIKE :code')
                ->setParameter('code', "%{$search->code}%")
            ;
        }

        if (!empty($search->designation)) {
            $query = $query
                ->andWhere('p.designation LIKE :designation')
                ->setParameter('designation', "%{$search->designation}%")
            ;
        }

        if (!empty($search->reference)) {
            $query = $query
                ->andWhere('p.reference LIKE :reference')
                ->setParameter('reference',"%{$search->reference}%")
            ;
        }

        if (!empty($search->place)) {
            $query = $query
                ->andWhere('s.place LIKE :place')
                ->setParameter('place', "%{$search->place}%");
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }
}