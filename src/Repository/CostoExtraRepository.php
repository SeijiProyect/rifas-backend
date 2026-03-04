<?php

namespace App\Repository;

use App\Entity\CostoExtra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method CostoExtra|null find($id, $lockMode = null, $lockVersion = null)
 * @method CostoExtra|null findOneBy(array $criteria, array $orderBy = null)
 * @method CostoExtra[]    findAll()
 * @method CostoExtra[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CostoExtraRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, CostoExtra::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return CostoExtra[] Returns an array of CostoExtra objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('c')
    ->andWhere('c.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('c.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CostoExtra
    {
    return $this->createQueryBuilder('c')
    ->andWhere('c.exampleField = :val')
    ->setParameter('val', $value)
    ->getQuery()
    ->getOneOrNullResult()
    ;
    }
    */

    public function deleteCostoExtra(CostoExtra $costo)
    {
        $this->manager->remove($costo);
        $this->manager->flush();
    }

    public function saveCostoExtra($pasajero, $descripcion, $monto)
    {
        $ce = new CostoExtra();

        $ce
            ->setPasajero($pasajero)
            ->setDescripcion($descripcion)
            ->setMonto($monto)
            ->setFecha(new \DateTime('now'));

        $this->manager->persist($ce);
        $this->manager->flush();

        return $ce;
    }

    public function costosExtrasByPasajero($pasajero)
    {
        $costos_extras = $this->createQueryBuilder('ce')
            ->select('ce.id, ce.Descripcion, ce.Monto ')
            ->leftJoin('ce.Pasajero', 'p')
            ->where("ce.Pasajero = :pas")
            ->setParameter('pas', $pasajero)
            ->getQuery()
            ->getResult();

        return $costos_extras;
    }

}