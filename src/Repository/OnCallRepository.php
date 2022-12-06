<?php

namespace App\Repository;

use App\Entity\OnCall;
use App\Data\SearchOnCall;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Oncall|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oncall|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oncall[]    findAll()
 * @method Oncall[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OnCallRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Oncall::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les rapports d'astreinte liés à une recherche
     *
     * @param SearchOnCall $search
     * @return PaginationInterface
     */
    public function findSearch(SearchOnCall $search, $organisation, $service): PaginationInterface
    {
        $query = $this->createQueryBuilder('o')
            ->select('o', 'u')
            ->join('o.user', 'u')
            ->orderBy('o.id', 'DESC');

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }
}
