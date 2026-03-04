<?php

namespace App\Repository;

use App\Entity\Rifa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rifa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rifa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rifa[]    findAll()
 * @method Rifa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RifaRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Rifa::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return Rifa[] Returns an array of Rifa objects
    //  */
    /*
    public function findByExampleField($value)
    {
    return $this->createQueryBuilder('r')
    ->andWhere('r.exampleField = :val')
    ->setParameter('val', $value)
    ->orderBy('r.id', 'ASC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult()
    ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Rifa
    {
    return $this->createQueryBuilder('r')
    ->andWhere('r.exampleField = :val')
    ->setParameter('val', $value)
    ->getQuery()
    ->getOneOrNullResult()
    ;
    }
    */

    public function deleteRifa(Rifa $rifa)
    {
        $this->manager->remove($rifa);
        $this->manager->flush();
    }

    public function updateRifa(Rifa $rifa)
    {
        $this->manager->persist($rifa);
        $this->manager->flush();
        return $rifa;
    }

    public function getRifaById($id)
    {
        $rifa= $this->find(array("id" => $id));
        if ($rifa) {
            return $rifa;
        } else {
            return null;
        }
    }

    public function saveRifa(Rifa $rifa)
    {
        $new_rifa = new Rifa();
        $new_rifa
            ->setNombre($rifa->getNombre())
            ->setDescripcion($rifa->getDescripcion())
            ->setFechaInicio($rifa->getFechaInicio())
            ->setFechaFin($rifa->getFechaFin())
            ->setOrganizacion($rifa->getOrganizacion())
            ->setActiva($rifa->getActiva());

        $this->manager->persist($new_rifa);
        $this->manager->flush();

        return $new_rifa;
    }

    public function listRifaActiva()
    {
        $estado = true;
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT r.id AS rifa_id, org.id AS organizacion_id, org.nombre AS organizador_nombre, r.nombre AS rifa_nombre,
        r.descripcion AS rifa_descripcion, r.fecha_inicio AS rifa_fecha_inicio, r.fecha_fin AS rifa_fecha_fin
        FROM rifa r 
        INNER JOIN organizacion org ON org.id = r.organizacion_id
        WHERE r.activa = '" . $estado . "'";

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
    }

}