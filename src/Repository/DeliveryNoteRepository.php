<?php

namespace App\Repository;

use App\Data\GlobalSearch;
use App\Entity\DeliveryNote;
use App\Data\SearchDeliveryNote;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @param  SearchWorkorder $search
     * @return PaginationInterface
     */
    public function findSearch(SearchDeliveryNote $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->select('d')
            ->join('d.provider', 'p')
            ->andWhere('d.organisation = :val')
            ->setParameter('val', $search->organisation);

        if (!empty($search->number)) {
            $query = $query
                ->andWhere('d.number LIKE :number')
                ->setParameter('number', "%{$search->number}%");
        }

        if (!empty($search->provider)) {
            $search->provider = strtoupper($search->provider);
            $query = $query
                ->andWhere('p.name LIKE :provider')
                ->setParameter('provider', "%{$search->provider}%");
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }

    public function findDeliveryNotesAppro($part)
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.id', 'DESC')
            ->select('d')
            ->join('d.deliveryNoteParts', 'p')
            ->andWhere('p.part = :val')
            ->setParameter('val', $part)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les machines liées à une recherche d'un mot
     *
     * @param Sorganisation
     * @param $globalSearch
     * 
     * @return DeliveryNote[]
     */
    public function findGlobalSearch($organisation, GlobalSearch $globalSearch)
    {
        $word =  "%".strtoupper($globalSearch->search)."%";
    
        return $this->createQueryBuilder('d')
            ->select('d')
            ->innerjoin('d.provider', 'p')
            ->andWhere('d.organisation = :organisation')

            ->andWhere(
                '
                p.name LIKE :word 
                OR 
                d.number LIKE :word
            '
            )

            ->setParameters(
                new ArrayCollection(
                    [
                    new Parameter('organisation', $organisation),
                    new Parameter('word', $word),
                    ]
                )
            ) 

            ->orderBy('d.number', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Fonction de recherche de bon de livraison selon le fournisseur
     *
     * @param  integer $organisation
     * @param  integer $provider
     * @return DeliveryNote[]
     */
    public function findDeliveryNoteByProvider($organisationId, $providerId)
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->where('d.organisation = :organisation')
            ->setParameter('organisation', $organisationId)
            ->andWhere('d.provider = :provider')
            ->setParameter('provider', $providerId)
            ->getQuery()
            ->getResult();
    }
}
