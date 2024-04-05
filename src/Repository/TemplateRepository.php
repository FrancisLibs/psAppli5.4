<?php

namespace App\Repository;

use App\Entity\Template;
use App\Data\SearchTemplate;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class TemplateRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Template::class);
        $this->paginator = $paginator;
    }

    /**
     * Récupère les templates liés à une recherche
     *
     * @param  SearchTemplate $search
     * @return PaginationInterface
     */
    public function findTemplates(SearchTemplate $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('t')
            ->select('t', 'm')
            ->leftJoin('t.machines', 'm')
            ->orderBy('t.createdAt', 'ASC')
            ->andWhere('t.organisation = :val')
            ->setParameter('val', $search->organisation)
            ->andWhere('t.active = :disabled')
            ->setParameter('disabled', true);

        if (!empty($search->machine)) {
            $machine = strtoupper($search->machine);
            // dd($machine);
            $query = $query
                ->andWhere('m.designation LIKE :machine')
                ->setParameter('machine', "%{$machine}%");
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }

    /**
     * Récupère le dernier template
     * 
     * @param $oragnisation
     */
    public function findLastTemplate($organisation)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.organisation = :val')
            ->setParameter('val', $organisation)
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les templates préventifs
     * 
     * @param $organisation
     */
    public function findAllActiveTemplates($organisationId)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.organisation = :val')
            ->setParameter('val', $organisationId)
            ->andWhere('t.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère 1 template par son id
     * 
     * @param $id
     */
    public function findOneById($id)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les bons de travail préventifs pour affichage calendrier
     *
     * @param int    $organisationId
     * @param string $year
     */
    public function findAllTemplatesForCalendar($organisationId, $year)
    {
        $beginDate = new \DateTime($year);

        return $this->createQueryBuilder('t')
            ->select('t')
            ->andWhere('t.organisation = :val')
            ->setParameter('val', $organisationId)
            ->andWhere('t.active = :enabled')
            ->setParameter('enabled', true)
            ->andWhere('t.nextDate >= :nextDate')
            ->setParameter('nextDate', $beginDate)
            ->getQuery()
            ->getResult();
    }
}
