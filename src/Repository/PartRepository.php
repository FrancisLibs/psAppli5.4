<?php

namespace App\Repository;

use App\Entity\Part;
use App\Data\SearchPart;
use App\Data\GlobalSearch;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
            ->setParameter('disabled', true);

        if (!empty($search->organisation)) {
            $query = $query
                ->andWhere('o.id = :organisation')
                ->setParameter('organisation', $search->organisation);
        }

        if (!empty($search->code)) {
            $code = strtoupper($search->code);
            $query = $query
                ->andWhere('p.code LIKE :code')
                ->setParameter('code', "%{$code}%");
        }

        if (!empty($search->designation)) {
            $designation = strtoupper($search->designation);
            $query = $query
                ->andWhere('p.designation LIKE :designation')
                ->setParameter('designation', "%{$designation}%");
        }

        if (!empty($search->reference)) {
            $reference = strtoupper($search->reference);
            $query = $query
                ->andWhere('p.reference LIKE :reference')
                ->setParameter('reference', "%{$reference}%");
        }

        if (!empty($search->place)) {
            $place = strtoupper($search->place);
            $query = $query
                ->andWhere('s.place LIKE :place')
                ->setParameter('place', "%{$place}%");
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
            ->select('p', 's', 'f', 'o')
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
            ->getResult();
    }

    /**
     * Retourne la liste des pièces pour la requête ajax dans la modale de demande de prix
     * @return Part[]
     */
    public function findParts($organisation)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
            ->orderBy('p.provider', 'ASC')
            //->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les pièces à réapprovisionner / fournisseur / organisation
     *
     * @param [type] $organisation
     * @param [type] $provider
     * 
     * @return Parts[]
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
            ->getResult();
    }

    /**
     * Récupère la valeur du stock pour une certaine organisation
     *
     * @param Sorganisation
     * 
     * @return void
     */
    public function findTotalStock($organisationId)
    {
        return $this->createQueryBuilder('p')
            ->join('p.stock', 's')
            ->select('SUM(p.steadyPrice * s.qteStock) as totalStock')
            ->where('p.organisation = :organisationId')
            ->setParameter('organisationId', $organisationId)
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les pièces liées à une recherche d'un mot
     *
     * @param Sorganisation
     * @param $globalSearch
     * 
     * @return Part[]
     */
    public function findGlobalSearch($organisation, GlobalSearch $globalSearch)
    {
        $word =  "%".strtoupper($globalSearch->search)."%";

        return $this->createQueryBuilder('p')
            ->select('p')
            ->andWhere('p.organisation = :organisation')

            ->andWhere('p.active = true')

            ->andWhere('
                p.code LIKE :word 
                OR 
                p.designation LIKE :word
                OR
                p.reference LIKE :word
                OR
                p.remark LIKE :word
            ')

            ->setParameters(new ArrayCollection([
                new Parameter('organisation', $organisation),
                new Parameter('word', $word),
            ]))

            ->orderBy('p.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return top value Parts[]
     */
    public function findTopValueParts($organisation)
    {
        return $this->createQueryBuilder('p')
            ->select('p', 's', 'o')
            ->join('p.stock', 's')
            ->join('p.organisation', 'o')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->orderBy('p.steadyPrice', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     * Fonction de recherche de pièces selon le fournisseur
     *
     * @param integer $organisation
     * @param integer $provider
     * @return Parts[]
     */
    public function findPartsByProvider($organisationId, $providerId)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisationId)
            ->andWhere('p.provider = :provider')
            ->setParameter('provider', $providerId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de pièces en réappro dont la date
     * de livraison prévue est plus petite que la date du jour
     * Donc la date est dépassée
     * 
     * @param integer $organisationid
     * 
     * @return Part[]
     */
    public function countLateParts($organisationId)
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->andWhere('p.organisation = :organisation')
            ->setParameter('organisation', $organisationId)
            ->andWhere('p.maxDeliveryDate < CURRENT_DATE()')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre de pièces en réappro et non achetées
     * 
     * @param integer $organisationid
     * 
     * @return Part[]
     */
    public function countPartsToBuy($organisationId)
    {
        return $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->join('p.stock', 's')
            ->where('p.organisation = :organisation')
            ->setParameter('organisation', $organisationId)
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
            ->andWhere('s.qteStock < s.qteMin')
            ->andWhere('s.approQte <= 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne une pièce selon l'organisation et le code de la pièce
     * 
     * @param integer $organisationid
     * @param string $code
     * 
     * @return Part[]
     */
    public function findPartByCode($organisationId, $code)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->andWhere('p.organisation = :organisationId')
            ->setParameter('organisationId', $organisationId)
            ->andWhere('p.active = :disabled')
            ->setParameter('disabled', true)
            ->andWhere('p.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


}
