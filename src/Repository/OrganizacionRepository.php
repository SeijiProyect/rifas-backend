<?php

namespace App\Repository;

use App\Entity\Organizacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Organizacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organizacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organizacion[]    findAll()
 * @method Organizacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizacionRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Organizacion::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return Organizacion[] Returns an array of Organizacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Organizacion
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getOrganizadorById($id)
    {
        $organizacion = $this->find(array("id" => $id));
        if ($organizacion) {
            return $organizacion;
        } else {
            return null;
        }
    }
}
