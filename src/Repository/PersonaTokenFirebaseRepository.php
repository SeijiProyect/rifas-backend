<?php

namespace App\Repository;

use App\Entity\PersonaTokenFirebase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonaTokenFirebase>
 *
 * @method PersonaTokenFirebase|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonaTokenFirebase|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonaTokenFirebase[]    findAll()
 * @method PersonaTokenFirebase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonaTokenFirebaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonaTokenFirebase::class);
    }

    public function add(PersonaTokenFirebase $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PersonaTokenFirebase $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PersonaTokenFirebase[] Returns an array of PersonaTokenFirebase objects
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

//    public function findOneBySomeField($value): ?PersonaTokenFirebase
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
