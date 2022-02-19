<?php

namespace App\Repository;

use App\Entity\Part;
use App\Data\SearchPart;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
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
     * Récupère les pièces liées à une recherche
     *
     * @param SearchPart $search
     * @return PaginationInterface
     */
    public function findSearch(SearchPart $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->orderBy('p.code', 'ASC')
            ->select('p', 's', 'o')
            ->join('p.stock', 's')
            ->join('p.organisation', 'o')
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
        ;
        
        if (!empty($search->organisation)) {
            $query = $query
                ->andWhere('o.id = :organisation')
                ->setParameter('organisation', $search->organisation);
        }

        if (!empty($search->code)) {
            $code = strtoupper($search->code);
            $query = $query
                ->andWhere('p.code LIKE :code')
                ->setParameter('code', "%{$code}%")
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
            $place = strtoupper($search->place);
            $query = $query
                ->andWhere('s.place LIKE :place')
                ->setParameter('place', "%{$place}%")
            ;
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            20
        );
    }

    /**
     * @return Part[]
     */
    public function findPartsToBuy($organisation)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 's')
            ->join('p.stock', 's')
            ->join('p.organisation', 'o')
            ->join('p.provider', 'f')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
            ->andWhere('s.qteStock < s.qteMin')
            ->orderBy('p.provider', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

        // SELECT p.code, p.reference, p.designation, pro.name
        // FROM part p 
        // INNER JOIN stock s ON p.id = s.part_id
        // INNER JOIN provider pro ON pro.id = p.provider_id
        // WHERE s.qte_stock <= s.qte_min
        // ORDER BY pro.name

    /**
     * @return Part[]
     */
    public function findParts($organisation)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 's', 'o', 'f')
            ->join('p.stock', 's')
            ->join('p.organisation', 'o')
            ->join('p.provider', 'f')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->orderBy('p.provider', 'ASC')
            //->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }
    /**
     * Undocumented function
     *
     * @param [type] $organisation
     * @param [type] $provider
     * @return void
     */
    public function findProviderParts($organisation, $provider)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 's')
            ->join('p.stock', 's')
            ->join('p.organisation', 'o')
            ->join('p.provider', 'f')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
            ->andWhere('p.provider = :provider')
            ->setParameter('provider', $provider)
            ->andWhere('s.qteStock < s.qteMin')
            ->orderBy('p.provider', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
