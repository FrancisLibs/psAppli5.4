<?php

namespace App\Repository;

use App\Data\SearchPreventive;
use App\Entity\Workorder;
use App\Data\SearchWorkorder;
use DateTime;
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
     * Récupère tous le bons de travail d'une organisation
     * @param int $organisation
     * @return Workorder[] Returns an array of workorder
     */

    public function findByOrganisation($organisation)
    {
        return $this->createQueryBuilder('w')
            ->select('w', 's')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $organisation)
            ->andWhere('s.name <> :status')
            ->setParameter('status', 'CLOTURE')
            ->orderBy('w.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les bons de travail liés à une recherche
     *
     * @param SearchWorkorder $search
     * @return PaginationInterface
     */
    public function findSearch(SearchWorkorder $search): PaginationInterface
    {
        //dd($search->status->getId());
        $query = $this->createQueryBuilder('w')
            ->select('w', 'm', 'u', 'o', 's')
            ->join('w.machines', 'm')
            ->join('w.user', 'u')
            ->join('w.organisation', 'o')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $search->organisation)
            ->orderBy('w.id', 'DESC');

        if (!empty($search->id)) {
            $query = $query
                ->andWhere('w.id = :id')
                ->setParameter('id', $search->id);
        }

        if (!empty($search->machine)) {
            $machine = strtoupper($search->machine);
            $query = $query
                ->andWhere('m.designation LIKE :machine')
                ->setParameter('machine', "%{$machine}%");
        }

        if (!empty($search->user)) {
            $query = $query
                ->andWhere('u.id = :user')
                ->setParameter('user', $search->user);
        }

        if (!empty($search->status)) {
            $status = $search->status->getId();
            $query = $query
                ->andWhere('w.workorderStatus <> 5')
                ->andWhere('w.workorderStatus = :status')
                ->setParameter('status', $status);
        }

        if (empty($search->closed)) {
            $query = $query
                ->andWhere('w.workorderStatus <> 5');
        }

        if (!empty($search->closed)) {
            $query = $query
                ->andWhere('w.workorderStatus = :cloture')
                ->setParameter('cloture', 5);
        }

        if (!empty($search->preventive)) {
            $query = $query
                ->andWhere('w.preventive = :disabled')
                ->setParameter('disabled', $search->preventive);
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            15
        );
    }

    /**
     * Compte les BT préventifs avec un numéro de template
     *
     * @param int $templateNumber
     */
    public function countPreventiveWorkorder($templateNumber)
    {
        return $this->createQueryBuilder('w')
            ->select('count(w.id)')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.templateNumber = :val')
            ->setParameter('val', $templateNumber)
            ->andWhere('s.name <> :status')
            ->setParameter('status', 'CLOTURE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les bons de travail préventifs
     *
     * @param int $organisationId
     */
    public function findAllLatePreventiveWorkorders($organisationId)
    {
        return $this->createQueryBuilder('w')
            ->select('w', 's')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $organisationId)
            ->andWhere('w.preventive = :enabled')
            ->setParameter('enabled', true)
            ->andWhere('s.name = :status')
            ->setParameter('status', 'EN_RETARD')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les bons de travail préventifs
     *
     * @param int $organisationId
     */
    public function findAllActivePreventiveWorkorders($organisationId)
    {
        return $this->createQueryBuilder('w')
            ->select('w', 's')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $organisationId)
            ->andWhere('w.preventive = :enabled')
            ->setParameter('enabled', true)
            ->andWhere("s.name = 'EN_COURS' OR s.name = 'EN_PREP.'")
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les bons de travail préventifs
     *
     * @param int $organisationId
     */
    public function findIndicatorsWorkorders($organisation, $year)
    {
        $date = new DateTime($year);

        return $this->createQueryBuilder('w')
            ->select('w', 'm')
            ->join('w.workorderStatus', 's')
            ->join('w.machines', 'm')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $organisation)
            ->andWhere('w.startDate > :year')
            ->setParameter('year', $date)
            ->andWhere('w.preventive = :preventive')
            ->setParameter('preventive', false)
            ->andWhere('w.durationDay > 0 OR w.durationHour > 0 OR w.durationMinute > 0')
            ->getQuery()
            ->getResult();
    }
}
