<?php

namespace App\Repository;

use App\Entity\StockValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StockValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockValue[]    findAll()
 * @method StockValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockValue::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(StockValue $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(StockValue $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param int $organisation
     * @return StockValue by organisation
     */
    public function findStockValues($organisation)
    {
        return $this->createQueryBuilder('s')
            ->where('s.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->getQuery()
            ->getResult()
        ;
    }
}
