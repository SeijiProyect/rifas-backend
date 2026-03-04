<?php

namespace App\Repository;

use App\Entity\PasajeroNotificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasajeroNotificacion>
 *
 * @method PasajeroNotificacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasajeroNotificacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasajeroNotificacion[]    findAll()
 * @method PasajeroNotificacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasajeroNotificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasajeroNotificacion::class);
    }

    public function add(PasajeroNotificacion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasajeroNotificacion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PasajeroNotificacion[] Returns an array of PasajeroNotificacion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PasajeroNotificacion
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
