<?php

namespace App\Repository;

use App\Entity\CiudadCampos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CiudadCampos>
 *
 * @method CiudadCampos|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiudadCampos|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiudadCampos[]    findAll()
 * @method CiudadCampos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiudadCamposRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CiudadCampos::class);
    }

    public function add(CiudadCampos $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CiudadCampos $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilter($termino, $ciudad, $desde, $limit)
    {
        $ciudadCampos = $this->createQueryBuilder('d')
            ->select('d.id, ci.id as ciudad_id, ci.nombre as ciudad_nombre, d.nombre, d.valor')
            ->leftJoin('d.ciudad', 'ci')
            ->groupBy('d.id')
            ->orderBy('d.nombre');
        
        if ($ciudad != 'todos') {
            $ciudadCampos->andWhere("ci.id = :ciudad")
                ->setParameter('ciudad', $ciudad);
        }

        if ($termino != '') {
            $ciudadCampos->andWhere('d.nombre LIKE :term OR ci.nombre LIKE :term OR d.valor LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $ciudadCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTermino($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, ci.id as ciudad_id, ci.nombre as ciudad_nombre, d.nombre, d.valor')
            ->leftJoin('d.ciudad', 'ci')
            ->groupBy('d.id')
            ->orderBy('d.nombre')
            ->andWhere('d.nombre LIKE :term OR ci.nombre LIKE :term OR d.valor LIKE :term OR d.id = :termEquals')
            ->setParameter('term', '%' . $termino . '%')
            ->setParameter('termEquals', $termino)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCiudad($ciudad, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, ci.id as ciudad_id, ci.nombre as ciudad_nombre, d.nombre, d.valor')
            ->leftJoin('d.ciudad', 'ci')
            ->groupBy('d.id')
            ->orderBy('d.nombre')
            ->andWhere('ci.id = :termEquals')
            ->setParameter('termEquals', $ciudad)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return CiudadCampos[] Returns an array of CiudadCampos objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CiudadCampos
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
