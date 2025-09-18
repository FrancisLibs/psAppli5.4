<?php

namespace App\Repository;

use App\Entity\Order;
use App\Data\SearchOrder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

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
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.number', 'ASC')
            ->select('p', 'o')
            ->join('p.organisation', 'o')
            ->setParameter('disabled', true);

        if (!empty($search->organisation)) {
            $query = $query
                ->andWhere('o.id = :organisation')
                ->setParameter('organisation', $search->organisation);
        }

        if (!empty($search->designation)) {
            $designation = strtoupper($search->designation);
            $query = $query
                ->andWhere('p.designation LIKE :designation')
                ->setParameter('designation', "%{$designation}%");
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