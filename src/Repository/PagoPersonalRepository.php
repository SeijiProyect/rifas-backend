<?php

namespace App\Repository;

use App\Entity\PagoPersonal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PagoPersonal|null find($id, $lockMode = null, $lockVersion = null)
 * @method PagoPersonal|null findOneBy(array $criteria, array $orderBy = null)
 * @method PagoPersonal[]    findAll()
 * @method PagoPersonal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoPersonalRepository extends ServiceEntityRepository
{

    private $manager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, PagoPersonal::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return PagoPersonal[] Returns an array of PagoPersonal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PagoPersonal
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function deletePagoPersonal( PagoPersonal $pp ) {
        $this->manager->remove($pp);
        $this->manager->flush();
    }

    public function savePagoPersonal($monto, $deposito)
    {
        $pp = new PagoPersonal();

        $pp
            ->setMonto($monto)
            ->setDeposito($deposito)
            ->setFecha(new \DateTime('now'));

        $this->manager->persist($pp);
        $this->manager->flush();
    }

    public function pagosPersonalesByDeposito($deposito)
    {
        $pagPersonales = $this->createQueryBuilder('pp')
        ->select('pp.id, pp.Fecha, pp.Monto, SUM(pp.Monto) as total')
        ->where("pp.Deposito = :dep")
        ->setParameter('dep', $deposito)
        ->getQuery()
        ->getResult();
        return $pagPersonales;
    }
}
