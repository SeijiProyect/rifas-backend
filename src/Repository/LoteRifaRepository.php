<?php

namespace App\Repository;

use App\Entity\LoteRifa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LoteRifa|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoteRifa|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoteRifa[]    findAll()
 * @method LoteRifa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoteRifaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoteRifa::class);
    }

    // /**
    //  * @return LoteRifa[] Returns an array of LoteRifa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LoteRifa
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
