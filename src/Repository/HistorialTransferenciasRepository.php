<?php

namespace App\Repository;

use App\Entity\HistorialTransferencias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method HistorialTransferencias|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistorialTransferencias|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistorialTransferencias[]    findAll()
 * @method HistorialTransferencias[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistorialTransferenciasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, HistorialTransferencias::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return HistorialTransferencias[] Returns an array of HistorialTransferencias objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HistorialTransferencias
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function saveHistorialTransferencias($pasajero, $talon, $accion)
    {
        $newHistorial = new HistorialTransferencias();
        $newHistorial->setPasajero($pasajero);
        $newHistorial->setTalon($talon);
        $newHistorial->setAccion($accion);
        $newHistorial->setFecha(new \DateTime('now'));

        $this->manager->persist($newHistorial);
        $this->manager->flush();

        return $newHistorial;
    }
}
