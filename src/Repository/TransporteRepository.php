<?php

namespace App\Repository;

use App\Entity\Transporte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transporte>
 *
 * @method Transporte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transporte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transporte[]    findAll()
 * @method Transporte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransporteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transporte::class);
    }

    public function add(Transporte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transporte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTransporteByTrayectoId($id)
    {
        $transporte = $this->findBy(array("trayecto" => $id));
        if ($transporte) {
            return $transporte;
        } else {
            return null;
        }
    }

    public function getTransporteByTrayectoPadreId($id)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.trayecto_padre = :val')
            ->setParameter('val', $id)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function getTransporteByCiudadInicioId($ciudad_inicio_id, $desde, $limit)
    {
        return $this->createQueryBuilder('t')
        ->select('t.id')
        ->leftJoin('t.trayecto', 'tr')
        ->where('tr.ciudad_inicio = :termEquals')
        ->setParameter('termEquals', $ciudad_inicio_id)
        ->groupBy('t.id')
        ->orderBy('t.id')
        ->setFirstResult($desde)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }

    public function getTodosTransporteByCiudadInicioId($ciudad_inicio_id)
    {
        return $this->createQueryBuilder('t')
        ->select('t.id')
        ->leftJoin('t.trayecto', 'tr')
        ->where('tr.ciudad_inicio = :termEquals')
        ->setParameter('termEquals', $ciudad_inicio_id)
        ->groupBy('t.id')
        ->orderBy('t.id')
        ->getQuery()
        ->getResult();
    }

    public function getTransporteByCiudadFinId($ciudad_fin_id, $desde, $limit)
    {
        return $this->createQueryBuilder('t')
        ->select('t.id')
        ->leftJoin('t.trayecto', 'tr')
        ->where('tr.ciudad_fin = :termEquals')
        ->setParameter('termEquals', $ciudad_fin_id)
        ->groupBy('t.id')
        ->orderBy('t.id')
        ->setFirstResult($desde)
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }

    public function getTodosTransporteByCiudadFinId($ciudad_fin_id)
    {
        return $this->createQueryBuilder('t')
        ->select('t.id')
        ->leftJoin('t.trayecto', 'tr')
        ->where('tr.ciudad_fin = :termEquals')
        ->setParameter('termEquals', $ciudad_fin_id)
        ->groupBy('t.id')
        ->orderBy('t.id')
        ->getQuery()
        ->getResult();
    }

    //    /**
    //     * @return Transporte[] Returns an array of Transporte objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transporte
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
