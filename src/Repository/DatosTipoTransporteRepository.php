<?php

namespace App\Repository;

use App\Entity\DatosTipoTransporte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DatosTipoTransporte>
 *
 * @method DatosTipoTransporte|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatosTipoTransporte|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatosTipoTransporte[]    findAll()
 * @method DatosTipoTransporte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatosTipoTransporteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatosTipoTransporte::class);
    }

    public function add(DatosTipoTransporte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DatosTipoTransporte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTransporteTipoByTransporteId($id)
    {
        $transportesTipo = $this->findBy(array("transporte" => $id));
        if ($transportesTipo) {
            return $transportesTipo;
        } else {
            return null;
        }
    }

    public function findByTransporte($transporte)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id')
            ->andWhere('t.id = :termEquals')
            ->setParameter('termEquals', $transporte)
            ->getQuery()
            ->getResult();
    }

    public function findByCampo($campo, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id')
            ->andWhere('cpt.id = :termEquals')
            ->setParameter('termEquals', $campo)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTipo($tipo, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id')
            ->andWhere('t.id = :termEquals')
            ->setParameter('termEquals', $tipo)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByAeropuerto($aeropuerto, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id')
            ->andWhere('a.id = :termEquals')
            ->setParameter('termEquals', $aeropuerto)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTermino($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id')
            ->andWhere('d.valor LIKE :term OR tp.nombre LIKE :term OR cpt.nombre LIKE :term OR a.nombre LIKE :term OR d.id = :termEquals')
            ->setParameter('term', '%' . $termino . '%')
            ->setParameter('termEquals', $termino)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCampoAero($campo, $aeropuerto, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');
        
        if ($campo != 'todos') {
            $transporteCampos->andWhere("cpt.id = :campos_tipo_transporte")
                ->setParameter('campos_tipo_transporte', $campo);
        }

        if ($aeropuerto != 'todos') {
            $transporteCampos->andWhere("a.id = :aereopuerto")
                ->setParameter('aereopuerto', $aeropuerto);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCampoTipo($campo, $tipo, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');
        
        if ($campo != 'todos') {
            $transporteCampos->andWhere("cpt.id = :campos_tipo_transporte")
                ->setParameter('campos_tipo_transporte', $campo);
        }

        if ($tipo != 'todos') {
            $transporteCampos->andWhere("t.id = :transporte")
                ->setParameter('transporte', $tipo);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByAeroTipo($aeropuerto, $tipo, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');

        if ($tipo != 'todos') {
            $transporteCampos->andWhere("t.id = :transporte")
                ->setParameter('transporte', $tipo);
        }

        if ($aeropuerto != 'todos') {
            $transporteCampos->andWhere("a.id = :aereopuerto")
                ->setParameter('aereopuerto', $aeropuerto);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySelect($campo, $tipo, $aeropuerto, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');

        if ($campo != 'todos') {
            $transporteCampos->andWhere("cpt.id = :campos_tipo_transporte")
                ->setParameter('campos_tipo_transporte', $campo);
        }

        if ($tipo != 'todos') {
            $transporteCampos->andWhere("t.id = :transporte")
                ->setParameter('transporte', $tipo);
        }

        if ($aeropuerto != 'todos') {
            $transporteCampos->andWhere("a.id = :aereopuerto")
                ->setParameter('aereopuerto', $aeropuerto);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByAll($termino, $campo, $tipo, $aeropuerto, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');

        if ($campo != 'todos') {
            $transporteCampos->andWhere("cpt.id = :campos_tipo_transporte")
                ->setParameter('campos_tipo_transporte', $campo);
        }

        if ($tipo != 'todos') {
            $transporteCampos->andWhere("t.id = :transporte")
                ->setParameter('transporte', $tipo);
        }

        if ($aeropuerto != 'todos') {
            $transporteCampos->andWhere("a.id = :aereopuerto")
                ->setParameter('aereopuerto', $aeropuerto);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('d.valor LIKE :term OR tp.nombre LIKE :term OR cpt.nombre LIKE :term OR a.nombre LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTerminoCampo($termino, $campo, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');

        if ($campo != 'todos') {
            $transporteCampos->andWhere("cpt.id = :campos_tipo_transporte")
                ->setParameter('campos_tipo_transporte', $campo);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('d.valor LIKE :term OR tp.nombre LIKE :term OR cpt.nombre LIKE :term OR a.nombre LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTerminoTipo($termino, $tipo, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');

        if ($tipo != 'todos') {
            $transporteCampos->andWhere("t.id = :transporte")
                ->setParameter('transporte', $tipo);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('d.valor LIKE :term OR tp.nombre LIKE :term OR cpt.nombre LIKE :term OR a.nombre LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByTerminoAero($termino, $aeropuerto, $desde, $limit)
    {
        $transporteCampos = $this->createQueryBuilder('d')
            ->select('d.id, t.id as transporte_id, tp.nombre as transporte_tipo, ci.nombre as ciudad_inicio, cf.nombre as ciudad_fin, cpt.id as campo_tipo_id, cpt.nombre as campo_tipo_nombre, a.id as aeropuerto_id, a.nombre as aeropuerto_nombre, d.valor')
            ->leftJoin('d.campos_tipo_transporte', 'cpt')
            ->leftJoin('d.transporte', 't')
            ->leftJoin('t.transporte_tipo', 'tp')
            ->leftJoin('t.trayecto', 'ty')
            ->leftJoin('ty.ciudad_inicio', 'ci')
            ->leftJoin('ty.ciudad_fin', 'cf')
            ->leftJoin('d.aereopuerto', 'a')
            ->groupBy('d.id')
            ->orderBy('d.id');

        if ($aeropuerto != 'todos') {
            $transporteCampos->andWhere("a.id = :aereopuerto")
                ->setParameter('aereopuerto', $aeropuerto);
        }

        if ($termino != '') {
            $transporteCampos->andWhere('d.valor LIKE :term OR tp.nombre LIKE :term OR cpt.nombre LIKE :term OR a.nombre LIKE :term OR d.id = :termEquals')
                ->setParameter('term', '%' . $termino . '%')
                ->setParameter('termEquals', $termino);
        }

        return $transporteCampos->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    

    //    /**
    //     * @return DatosTipoTransporte[] Returns an array of DatosTipoTransporte objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DatosTipoTransporte
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
