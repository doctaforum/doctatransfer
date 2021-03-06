<?php

namespace App\Repository;

use App\Entity\Admin;
use App\Entity\File;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }


    /**
     * @return File[] Returns an array of File objects
     */
    public function findByUser(Admin $user)
    {
        return $this->createQueryBuilder('f')
            ->select()
            ->where('f.admin = :user')
            ->andWhere('f.isDeleted != :isDeleted OR f.isDeleted IS NULL')
            ->setParameter('user', $user)
            ->setParameter('isDeleted', true)
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return File[] Returns an array of File objects
     */
    public function findByAntiquity(DateTime $datetime)
    {
        return $this->createQueryBuilder('f')
            ->select()
            ->where('f.create_date < :val')
            ->setParameter('val', $datetime)
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @return File[] Returns an array of File objects
     */
    public function findExpired(DateTime $datetime)
    {
        return $this->createQueryBuilder('f')
            ->select()
            ->where('f.expiration_date <= :expirationDate')
            ->setParameter('expirationDate', $datetime)
            ->getQuery()
            ->getResult()
        ;
    }


    // /**
    //  * @return File[] Returns an array of File objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?File
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
