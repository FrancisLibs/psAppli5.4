<?php

namespace App\Repository;

use App\Entity\DeliveryNote;
use App\Data\SearchDeliveryNote;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DeliveryNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryNote[]    findAll()
 * @method DeliveryNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryNoteRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, DeliveryNote::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les bons de livraison liés à une recherche
     *
     * @param SearchWorkorder $search
     * @return PaginationInterface
     */
    public function findSearch(SearchDeliveryNote $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->select('d')
            ->andWhere('d.organisation = :val')
            ->setParameter('val', $search->organisation);

        if (!empty($search->number)) {
            $query = $query
                ->andWhere('d.number = :number')
                ->setParameter('number', "%{$search->number}%");
        }

        if (!empty($search->user)) {
            $query = $query
                ->andWhere('d.provider = :provider')
                ->setParameter('provider', $search->provider);
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }
}
