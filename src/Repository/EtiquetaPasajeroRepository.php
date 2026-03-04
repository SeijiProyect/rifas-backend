<?php

namespace App\Repository;

use App\Entity\EtiquetaPasajero;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtiquetaPasajero|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtiquetaPasajero|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtiquetaPasajero[]    findAll()
 * @method EtiquetaPasajero[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtiquetaPasajeroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtiquetaPasajero::class);
    }

    // /**
    //  * @return EtiquetaPasajero[] Returns an array of EtiquetaPasajero objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EtiquetaPasajero
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
