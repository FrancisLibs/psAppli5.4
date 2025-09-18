<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Organisation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }


    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user, 
        string $newHashedPassword
    ): void {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.', 
                    \get_class($user)
                )
            );
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Récupère tous les utilisateurs d'une organisation
     *
     * @param  int $organisation
     * @return Users[] Returns an array of users
     */

    public function findAllActive()
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.active = :disabled')
            ->setParameter('disabled', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les utilisateurs actifs d'une organisation
     *
     * @param  int $organisation
     * @return Users[] Returns an array of users
     */

    public function findAllUsersByOrganisationAndActive(?Organisation $organisation): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.active = :active')
            ->setParameter('active', true)
            ;

            if ($organisation) {
                $qb->andWhere('u.organisation = :organisation')
                ->setParameter('organisation', $organisation);
            }
            return $qb;
    }

    public function findAllActiveUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.active = :disabled')
            ->setParameter('disabled', true);
    }

    public function findAllActiveUserByOrganisationAndService($userParams)
    {
        $organisation = $userParams[0];
        $service = $userParams[1];
        return $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.active = :disabled')
            ->setParameter('disabled', true)
            ->andWhere('u.organisation = :organisation')
            ->setParameter('organisation', $organisation)
            ->andWhere('u.service = :service')
            ->setParameter('service', $service)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
