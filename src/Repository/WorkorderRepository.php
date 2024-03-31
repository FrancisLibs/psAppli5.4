<?php

namespace App\Repository;

use DateTime;
use App\Entity\User;
use App\Entity\Workorder;
use App\Data\GlobalSearch;
use App\Data\SearchIndicator;
use App\Data\SearchWorkorder;
use App\Data\SearchPreventive;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class WorkorderRepository extends ServiceEntityRepository
{
    protected $paginator;


    public function __construct(
        ManagerRegistry $registry,
        PaginatorInterface $paginator,
    ) {
        parent::__construct($registry, Workorder::class);
        $this->paginator = $paginator;
    }


    /**
     * Récupère tous le bons de travail d'une organisation
     *
     * @param int $organisation
     * 
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
     * 
     * @return PaginationInterface
     */
    public function findSearch(SearchWorkorder $search): PaginationInterface
    {
        $query = $this->createQueryBuilder('w')
            ->select('w', 'm', 'u', 'o', 's')
            ->join('w.machines', 'm')
            ->join('w.user', 'u')
            ->join('w.organisation', 'o')
            ->join('w.workorderStatus', 's')
            // ->andWhere('w.organisation = :val')
            // ->setParameter('val', $search->organisation)
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

        if (!empty($search->request)) {
            $query = $query
                ->andWhere('w.request LIKE :request')
                ->setParameter('request', "%{$search->request}%");
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

        if (!empty($search->startDate)) {
            $startDate = $search->startDate;
            $endDate = clone $startDate;

            $startDate->setTime(0, 0, 0); // Réglage de l'heure à 00:00:00 pour inclure toute la journée.
            $endDate->setTime(23, 59, 59); // Réglage de l'heure à 23:59:59 pour inclure toute la journée.

            $query = $query
                ->andWhere('w.startDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        $query = $query->getQuery();

        return $this->paginator->paginate(
            $query,
            $search->page,
            20
        );
    }

    /**
     * Compte tous les BT préventifs actifs avec un numéro de template précis
     * et qui ne sont pas cloturés 
     * 
     * @param int $templateNumber
     * 
     * @return int
     */
    public function countPreventiveActiveWorkorder($templateNumber)
    {
        return $this->createQueryBuilder('w')
            ->select('count(w.id)')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.templateNumber = :val')
            ->setParameter('val', $templateNumber)
            ->andWhere("s.name <> :val1")
            ->setParameter('val1', 'CLOTURE')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les bons de travail préventifs
     *
     * @param int $organisationId
     * 
     * @return workorders Returns an array of workorder
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
     * Récupère les bons de travail préventifs en cours
     *
     * @param int $organisationId
     * @param int $organisationId
     * 
     * @return workorder[] Returns an array of workorder
     */
    public function findAllActivePreventiveWorkorders($organisationId)
    {
        return $this->createQueryBuilder('w')
            ->select('w', 's')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.organisation = :organisation')
            ->andWhere('w.preventive = :enabled')
            ->andWhere('s.name <> :val1 AND s.name <> :val2')
            ->setParameters(
                new ArrayCollection(
                    [
                    new Parameter('organisation', $organisationId),
                    new Parameter('enabled', true),
                    new Parameter('val1', 'CLOTURE'),
                    new Parameter('val2', 'TERMINE'),
                    ]
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les bons de travail préventifs
     *
     * @param int             $organisation
     * @param SearchIndicator $searchIndicator
     * 
     * @return workorders Returns an array of workorder
     */
    public function findIndicatorsWorkorders($organisation, $searchIndicator)
    {
        $startDate = $searchIndicator->startDate;
        $endDate = $searchIndicator->endDate;


        return $this->createQueryBuilder('w')
            ->select('w', 'm', 's')
            ->join('w.workorderStatus', 's')
            ->join('w.machines', 'm')
            ->andWhere('w.organisation = :val')
            ->setParameter('val', $organisation)
            ->andWhere('m.active = true')
            ->andWhere('w.startDate > :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('w.startDate < :endDate')
            ->setParameter('endDate', $endDate)
            ->andWhere('w.durationDay > 0 OR w.durationHour > 0 OR w.durationMinute > 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les machines liées à une recherche d'un mot
     *
     * @param int Sorganisation
     * @param GlobalSearch      $globalSearch
     * 
     * @return Part[]
     */
    public function findGlobalSearch($organisation, GlobalSearch $globalSearch)
    {
        $uppercaseWord =  "%" . strtoupper($globalSearch->search) . "%";
        $word = "%" . $globalSearch->search . "%";

        return $this->createQueryBuilder('w')
            ->select('w', 'u')
            ->join('w.user', 'u')

            ->andWhere('w.organisation = :organisation')

            ->andWhere(
                '
                w.remark LIKE :uppercaseWord
                OR 
                w.request LIKE :uppercaseWord
                OR
                w.implementation LIKE :uppercaseWord
                OR
                u.username LIKE :word
            '
            )

            ->setParameters(
                new ArrayCollection(
                    [
                    new Parameter('organisation', $organisation),
                    new Parameter('uppercaseWord', $uppercaseWord),
                    new Parameter('word', $word),
                    ]
                )
            )

            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Fetch workorders linked to a  machine
     *
     * @param int SorganisationId
     * @param SearchIndicator     $searchIndicator
     * @param int                 $machineId
     * 
     * @return workorders
     */
    public function findAllWorkordersByMachine(
        $organisationId, 
        $searchIndicator, 
        $machineId,
    ) {
        $startDate = $searchIndicator->startDate;
        $endDate = $searchIndicator->endDate;

        return $this->createQueryBuilder('w')
            ->select('w', 'o', 'm')
            ->join('w.organisation', 'o')
            ->join('w.machines', 'm')
            ->andWhere('o.id = :organisation')
            ->andWhere('m.id = :machine')
            ->andWhere('w.startDate >= :startDate')
            ->andWhere('w.startDate < :endDate')
            ->andWhere('w.durationDay > 0 OR w.durationHour > 0 OR w.durationMinute > 0')
            ->setParameters(
                new ArrayCollection(
                    [
                    new Parameter('organisation', $organisationId),
                    new Parameter('machine', $machineId),
                    new Parameter('startDate', $startDate),
                    new Parameter('endDate', $endDate),
                    ]
                )
            )
            ->getQuery()
            ->getResult();
    }

    /**
     * Count of the preentive late workorders
     *
     * @param int $organisationId
     * 
     * @return int
     */
    public function countLateBT($organisationId)
    {
        return $this->createQueryBuilder('w')
            ->select('count(w.id)')
            ->join('w.workorderStatus', 's')
            ->andWhere('w.organisation = :organisation')
            ->andWhere('w.preventive = :enabled')
            ->andWhere('s.name = :val1')
            ->setParameters(
                new ArrayCollection(
                    [
                    new Parameter('organisation', $organisationId),
                    new Parameter('enabled', true),
                    new Parameter('val1', 'EN_RETARD'),
                    ]
                )
            )
            ->getQuery()
            ->getSingleScalarResult();
    }
}
