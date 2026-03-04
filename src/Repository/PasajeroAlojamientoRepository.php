<?php

namespace App\Repository;

use App\Entity\PasajeroAlojamiento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasajeroAlojamiento>
 *
 * @method PasajeroAlojamiento|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasajeroAlojamiento|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasajeroAlojamiento[]    findAll()
 * @method PasajeroAlojamiento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasajeroAlojamientoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasajeroAlojamiento::class);
    }

    public function add(PasajeroAlojamiento $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasajeroAlojamiento $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PasajeroAlojamiento[] Returns an array of PasajeroAlojamiento objects
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

//    public function findOneBySomeField($value): ?PasajeroAlojamiento
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
