<?php

namespace App\Repository;

use App\Entity\CamposTipotransporte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CamposTipotransporte>
 *
 * @method CamposTipotransporte|null find($id, $lockMode = null, $lockVersion = null)
 * @method CamposTipotransporte|null findOneBy(array $criteria, array $orderBy = null)
 * @method CamposTipotransporte[]    findAll()
 * @method CamposTipotransporte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CamposTipotransporteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CamposTipotransporte::class);
    }

    public function add(CamposTipotransporte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CamposTipotransporte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilter($termino, $tipo, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, tt.id as tt_id, tt.nombre as tt_nombre, d.nombre, d.obligatorio, d.aeropuerto')
            ->leftJoin('d.transporte_tipo', 'tt')
            ->groupBy('d.id')
            ->orderBy('d.nombre');
        
        if ($tipo != 'todos') {
            $transporteCampos->andWhere("tt.id = :transporte_tipo")
                ->setParameter('transporte_tipo', $tipo);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('d.nombre LIKE :term OR tt.nombre LIKE :term OR d.obligatorio LIKE :term OR d.aeropuerto LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTermino($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, tt.id as tt_id, tt.nombre as tt_nombre, d.nombre, d.obligatorio, d.aeropuerto')
            ->leftJoin('d.transporte_tipo', 'tt')
            ->groupBy('d.id')
            ->orderBy('d.nombre')
            ->andWhere('d.nombre LIKE :term OR tt.nombre LIKE :term OR d.obligatorio LIKE :term OR d.aeropuerto LIKE :term OR d.id = :termEquals')
            ->setParameter('term', '%' . $termino . '%')
            ->setParameter('termEquals', $termino)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTransporteTipo($tipo, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, tt.id as tt_id, tt.nombre as tt_nombre, d.nombre, d.obligatorio, d.aeropuerto')
            ->leftJoin('d.transporte_tipo', 'tt')
            ->groupBy('d.id')
            ->orderBy('d.nombre')
            ->andWhere('tt.id = :termEquals')
            ->setParameter('termEquals', $tipo)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return CamposTipotransporte[] Returns an array of CamposTipotransporte objects
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

//    public function findOneBySomeField($value): ?CamposTipotransporte
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
