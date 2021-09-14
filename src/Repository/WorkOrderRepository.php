<?php

namespace App\Repository;

use App\Entity\Workorder;
use App\Data\SearchWorkorder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class WorkorderRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Workorder::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les machines liées à une recherche
     *
     * @param SearchWorkorder $search
     * @return PaginationInterface
     */
    public function findSearch(SearchWorkorder $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('w')
            ->orderBy('w.createdAt', 'ASC')
            ->select('w', 'm')
            ->join('w.machine', 'm');

        if (!empty($search->machine)) {
            $query = $query
                ->andWhere('m.machine LIKE :machine')
                ->setParameter('machine', "%{$search->machine}%");
        }

        if (!empty($search->user)) {
            $query = $query
                ->andWhere('w.user LIKE :user')
                ->setParameter('user', "%{$search->user}%");
        }

        if (!empty($search->status)) {
            $query = $query
                ->andWhere('w.status LIKE :status')
                ->setParameter('status', "%{$search->status}%");
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }
    
    /**
     * @return Workorder[] Returns an array of workorder
    */

    public function findByOrganisation($organisation)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $organisation)
            ->orderBy('w.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
