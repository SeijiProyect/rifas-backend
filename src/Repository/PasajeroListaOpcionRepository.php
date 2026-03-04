<?php

namespace App\Repository;

use App\Entity\PasajeroListaOpcion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasajeroListaOpcion>
 *
 * @method PasajeroListaOpcion|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasajeroListaOpcion|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasajeroListaOpcion[]    findAll()
 * @method PasajeroListaOpcion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasajeroListaOpcionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasajeroListaOpcion::class);
    }

    public function add(PasajeroListaOpcion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasajeroListaOpcion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PasajeroListaOpcion[] Returns an array of PasajeroListaOpcion objects
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

//    public function findOneBySomeField($value): ?PasajeroListaOpcion
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
