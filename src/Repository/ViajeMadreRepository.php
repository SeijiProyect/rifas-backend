<?php

namespace App\Repository;

use App\Entity\ViajeMadre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ViajeMadre|null find($id, $lockMode = null, $lockVersion = null)
 * @method ViajeMadre|null findOneBy(array $criteria, array $orderBy = null)
 * @method ViajeMadre[]    findAll()
 * @method ViajeMadre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViajeMadreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ViajeMadre::class);
    }

    // /**
    //  * @return ViajeMadre[] Returns an array of ViajeMadre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ViajeMadre
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
