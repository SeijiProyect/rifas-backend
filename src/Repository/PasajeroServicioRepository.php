<?php

namespace App\Repository;

use App\Entity\PasajeroServicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasajeroServicio>
 *
 * @method PasajeroServicio|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasajeroServicio|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasajeroServicio[]    findAll()
 * @method PasajeroServicio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasajeroServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasajeroServicio::class);
    }

    public function add(PasajeroServicio $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasajeroServicio $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PasajeroServicio[] Returns an array of PasajeroServicio objects
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

//    public function findOneBySomeField($value): ?PasajeroServicio
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
