<?php

namespace App\Repository;

use App\Entity\Servicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Servicio>
 *
 * @method Servicio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Servicio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Servicio[]    findAll()
 * @method Servicio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Servicio::class);
    }

    public function add(Servicio $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Servicio $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getServicioByTransporteId($id)
    {
        $servicio = $this->findBy(array("transporte" => $id));
        if ($servicio) {
            return $servicio;
        } else {
            return null;
        }
    }

    public function getServiciosByHospedaje($desde, $limit)
    {
        return $this->createQueryBuilder('s')
        ->select('s.id, s.transporte, s.hospedaje, s.cantidad, s.nro_booking, s.precio_por_persona, s.comentarios, s.estado, s.grupo')
        ->where('s.hospedaje != null')
        ->groupBy('s.id')
        ->orderBy('s.id')
        ->setFirstResult($desde)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }

    public function getTotalServiciosByHospedaje()
    {
        return $this->createQueryBuilder('s')
        ->select('s.id, s.transporte, s.hospedaje, s.cantidad, s.nro_booking, s.precio_por_persona, s.comentarios, s.estado, s.grupo')
        ->where('s.hospedaje != null')
        ->groupBy('s.id')
        ->orderBy('s.id')
        ->getQuery()
        ->getResult();
    }

    public function getServiciosByTransporte($desde, $limit)
    {
        return $this->createQueryBuilder('s')
        ->select('s.id, s.transporte, s.hospedaje, s.cantidad, s.nro_booking, s.precio_por_persona, s.comentarios, s.estado, s.grupo')
        ->where('s.transporte != null')
        ->groupBy('s.id')
        ->orderBy('s.id')
        ->setFirstResult($desde)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }

    public function getTotalServiciosByTransporte()
    {
        return $this->createQueryBuilder('s')
        ->select('s.id, s.transporte, s.hospedaje, s.cantidad, s.nro_booking, s.precio_por_persona, s.comentarios, s.estado, s.grupo')
        ->where('s.transporte != null')
        ->groupBy('s.id')
        ->orderBy('s.id')
        ->getQuery()
        ->getResult();
    }

//    /**
//     * @return Servicio[] Returns an array of Servicio objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Servicio
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
