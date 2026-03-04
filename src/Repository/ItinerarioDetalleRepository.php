<?php

namespace App\Repository;

use App\Entity\ItinerarioDetalle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItinerarioDetalle>
 *
 * @method ItinerarioDetalle|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItinerarioDetalle|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItinerarioDetalle[]    findAll()
 * @method ItinerarioDetalle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItinerarioDetalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItinerarioDetalle::class);
    }

    public function add(ItinerarioDetalle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ItinerarioDetalle $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByItinerario($itinerario, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC')
            ->andWhere('i.id = :termEquals')
            ->setParameter('termEquals', $itinerario)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCiudad($ciudad, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC')
            ->andWhere('c.id = :termEquals')
            ->setParameter('termEquals', $ciudad)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTrayecto($trayecto, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC')
            ->andWhere('ty.id = :termEquals')
            ->setParameter('termEquals', $trayecto)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTermino($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC')
            ->andWhere('i.Nombre LIKE :term OR c.nombre LIKE :term OR ci.nombre LIKE :term OR cf.nombre LIKE :term OR d.fecha_inicio LIKE :term OR d.fecha_fin LIKE :term OR d.id = :termEquals')
            ->setParameter('term', '%' . $termino . '%')
            ->setParameter('termEquals', $termino)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByItinerarioCiudad($itinerario, $ciudad, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($itinerario != 'todos') {
            $transporteCampos->andWhere("i.id = :itinerario")
                ->setParameter('itinerario', $itinerario);
        }

        if ($ciudad != 'todos') {
            $transporteCampos->andWhere("c.id = :ciudad")
                ->setParameter('ciudad', $ciudad);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByItinerarioTrayecto($itinerario, $trayecto, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($itinerario != 'todos') {
            $transporteCampos->andWhere("i.id = :itinerario")
                ->setParameter('itinerario', $itinerario);
        }

        if ($trayecto != 'todos') {
            $transporteCampos->andWhere("ty.id = :trayecto")
                ->setParameter('trayecto', $trayecto);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByItinerarioTermino($itinerario, $termino, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($itinerario != 'todos') {
            $transporteCampos->andWhere("i.id = :itinerario")
                ->setParameter('itinerario', $itinerario);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('i.Nombre LIKE :term OR c.nombre LIKE :term OR ci.nombre LIKE :term OR cf.nombre LIKE :term OR d.fecha_inicio LIKE :term OR d.fecha_fin LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCiudadTermino($ciudad, $termino, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($ciudad != 'todos') {
            $transporteCampos->andWhere("c.id = :ciudad")
                ->setParameter('ciudad', $ciudad);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('i.Nombre LIKE :term OR c.nombre LIKE :term OR ci.nombre LIKE :term OR cf.nombre LIKE :term OR d.fecha_inicio LIKE :term OR d.fecha_fin LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTrayectoTermino($trayecto, $termino, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($trayecto != 'todos') {
            $transporteCampos->andWhere("ty.id = :trayecto")
                ->setParameter('trayecto', $trayecto);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('i.Nombre LIKE :term OR c.nombre LIKE :term OR ci.nombre LIKE :term OR cf.nombre LIKE :term OR d.fecha_inicio LIKE :term OR d.fecha_fin LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTerminoItinerarioCiudad($termino, $itinerario, $ciudad, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($itinerario != 'todos') {
            $transporteCampos->andWhere("i.id = :itinerario")
                ->setParameter('itinerario', $itinerario);
        }

        if ($ciudad != 'todos') {
            $transporteCampos->andWhere("c.id = :ciudad")
                ->setParameter('ciudad', $ciudad);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('i.Nombre LIKE :term OR c.nombre LIKE :term OR ci.nombre LIKE :term OR cf.nombre LIKE :term OR d.fecha_inicio LIKE :term OR d.fecha_fin LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTerminoItinerarioTrayecto($termino, $itinerario, $trayecto, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, i.id as itinerario_id, i.Nombre as itinerario_nombre, c.id as ciudad_id, c.nombre as ciudad_nombre, ty.id as trayecto_id, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, d.fecha_inicio, d.fecha_fin, d.orden')
            ->leftJoin('d.itinerario', 'i')
            ->leftJoin('d.ciudad', 'c')
            ->leftJoin('d.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->groupBy('d.id')
            ->orderBy('d.orden', 'ASC');
        
        if ($itinerario != 'todos') {
            $transporteCampos->andWhere("i.id = :itinerario")
                ->setParameter('itinerario', $itinerario);
        }

        if ($trayecto != 'todos') {
            $transporteCampos->andWhere("ty.id = :trayecto")
                ->setParameter('trayecto', $trayecto);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('i.Nombre LIKE :term OR c.nombre LIKE :term OR ci.nombre LIKE :term OR cf.nombre LIKE :term OR d.fecha_inicio LIKE :term OR d.fecha_fin LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return ItinerarioDetalle[] Returns an array of ItinerarioDetalle objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ItinerarioDetalle
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
