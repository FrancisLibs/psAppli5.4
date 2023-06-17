<?php

namespace App\Repository;

use App\Entity\Provider;
use App\Data\GlobalSearch;
use App\Data\SearchProvider;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class ProviderRepository extends ServiceEntityRepository
{
    private $paginator;
    
    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Provider::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les bons de travail liés à une recherche
     *
     * @param Searchprovider $searchProvider
     * @return PaginationInterface
     */
    public function findSearch(SearchProvider $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.name', 'ASC');

        if (!empty($search->name)) {
            $name = strtoupper($search->name);
            $query = $query
                ->andWhere('p.name LIKE :name')
                ->setParameter('name', "%{$name}%");
        }

        if (!empty($search->city)) {
            $city = strtoupper($search->city);
            $query = $query
                ->andWhere('p.city LIKE :city')
                ->setParameter('city', "%{$city}%");
        }

        if (!empty($search->code)) {
            $code = strtoupper($search->code);
            $query = $query
                ->andWhere('p.code LIKE :code')
                ->setParameter('code', "%{$code}%");
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
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
    
        return $this->createQueryBuilder('p')
            ->select('p')
            ->andWhere('p.organisation = :organisation')

            ->andWhere('
                p.name LIKE :word 
                OR 
                p.city LIKE :word
                OR
                p.phone LIKE :word
            ')

            ->setParameters(new ArrayCollection([
                new Parameter('organisation', $organisation),
                new Parameter('word', $word),
            ])) 

            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
