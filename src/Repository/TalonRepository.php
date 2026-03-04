<?php

namespace App\Repository;

use App\Entity\Talon;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @method Talon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Talon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Talon[]    findAll()
 * @method Talon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TalonRepository extends ServiceEntityRepository
{
    private $manager;
    public $sorteos = array(
        [
            'sorteoNumero' => 1,
            'sorteoFecha' => '2023-08-24'
        ],
        [
            'sorteoNumero' => 2,
            'sorteoFecha' => '2023-09-29'
        ],
        [
            'sorteoNumero' => 3,
            'sorteoFecha' => '2023-10-27'
        ],
        [
            'sorteoNumero' => 4,
            'sorteoFecha' => '2023-11-24'
        ],
        [
            'sorteoNumero' => 5,
            'sorteoFecha' => '2023-12-29'
        ]
    );
    public $precioTalon = 20;


    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Talon::class);
        $this->manager = $entityManager;
    }

    public function updateTalon(Talon $talon): Talon
    {
        $talon->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->manager->persist($talon);
        $this->manager->flush();

        return $talon;
    }

    public function responseTalon(Talon $talon)
    {
        $comprador = $talon->getComprador() != null ? $talon->getComprador()->getNombre() : null;
        $deposito = $talon->getDeposito() != null ? $talon->getDeposito()->getId() : null;
        $solicitante = $talon->getSolicitante() != null ? $talon->getSolicitante()->getId() : null;

        $talResponse = array(
            'id' => $talon->getId(),
            'Comprador' => $comprador,
            'Pasajero' => $talon->getPasajero()->getId(),
            'Deposito' => $deposito,
            'Solicitante' => $solicitante,
            'Numero' => $talon->getNumero(),
            'FechaSorteo' => $talon->getFechaSorteo(),
            'Estado' => $talon->getEstado(),
            'Precio' => $talon->getPrecio(),
            'FechaRegistro' => $talon->getFechaRegistro(),
            'FechaEntrega' => $talon->getFechaEntrega(),
            'SorteoNumero' => $talon->getSorteoNumero()
        );

        return $talResponse;
    }

    public function insertEntrega($pasajero, $from, $to)
    {
        for ($i = $from; $i <= $to; $i++) {

            foreach ($this->sorteos as $sorteo) {
                $this->saveTalon(null, $pasajero, null, null, $i, $sorteo['sorteoFecha'], 'Pendiente de Pago', $this->precioTalon, null, $sorteo['sorteoNumero']);
            }
        }
    }

    public function insertEntregaTalon($sorteo, $pasajero, $from, $to)
    {
        $rsm = new ResultSetMapping();
        $qb = $this->manager->createNativeQuery('CALL TALON_P_EntregarTalon (' .
            ':idSorteo, :idPasajero, :desde, :hasta' .
            ')', $rsm);
        $qb->setParameters(
            array(
                'idPasajero' => $pasajero,
                'idSorteo' => $sorteo,
                'desde' => $from,
                'hasta' => $to
            )
        );
        //$qb->execute();
        $result = $qb->getResult();
        return $result;
    }

    public function saveTalon($comprador, $pasajero, $deposito, $solicitante, $numero, $fechaSorteo, $estado, $precio, $fechaRegistro, $sorteoNumero)
    {
        $tal = new Talon();

        $tal
            ->setComprador($comprador)
            ->setPasajero($pasajero)
            ->setDeposito($deposito)
            ->setSolicitante($solicitante)
            ->setNumero($numero)
            ->setFechaSorteo(new \DateTime($fechaSorteo))
            ->setEstado($estado)
            ->setPrecio($precio)
            ->setFechaRegistro($fechaRegistro)
            ->setFechaEntrega(new \DateTime('now'))
            ->setSorteoNumero($sorteoNumero)
            ->setCreatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($tal);
        $this->manager->flush();
    }

    public function talonesList($offset, $limit, $status, $pasajero, $desde, $hasta, $rifa, $sorteo)
    {
        $talones = $this->createQueryBuilder('t')
            ->select(
                't.id, 
            t.Numero, 
            t.SorteoNumero, 
            t.FechaSorteo, 
            t.Estado, 
            t.Precio, 
            t.FechaRegistro, 

            r.id as rifa_id,
            r.nombre as rifa_nombre,

            s.id as sorteo_id,
            s.sorteoNumero as sorteo_numero,
            s.fechaSorteo as fecha_sorteo,
            s.valorTalon as valor_talon,

            p.id as PasId, 
            p.Estado as PasajeroEstado,
            p.Comentarios as PasajeroComentarios,

            u.Nombre as UniversidadNombre,

            i.Nombre as ItinerarioNombre,
            
            v.Nombre as ViajeNombre,
            
            per.id as PerId,
            per.Nombres, 
            per.Apellidos, 
            per.Cedula,
            per.Celular,

            d.id as DepId,
            d.Monto,
            d.Tipo,
            d.Fecha,
            d.Comentario as DepositoComentario,

            c.id as CompradorId,
            c.Nombre as CompradorNombre, 
            c.Email as CompradorEmail, 
            c.Celular as CompradorCelular, 
            c.Departamento as CompradorDepartamento'
            )
            ->leftJoin('t.Pasajero', 'p')
            ->leftJoin('t.sorteo', 's')
            ->leftJoin('s.rifa', 'r')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('t.Comprador', 'c')
            ->leftJoin('p.Universidad', 'u')
            ->leftJoin('p.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->leftJoin('t.Deposito', 'd')
            ->orderBy('t.Numero, r.id, s.sorteoNumero', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($rifa != 'todos') {
            $talones->andWhere("r.id = :rifa_id")
                ->setParameter('rifa_id', $rifa);
        }

        if ($sorteo != 'todos') {
            $talones->andWhere("s.id = :sorteo_id")
                ->setParameter('sorteo_id', $sorteo);
        }

        if ($status != 'todos') {
            $talones->andWhere("t.Estado = :status")
                ->setParameter('status', $status);
        }

        if ($pasajero != 'todos' && $pasajero != '') {
            $talones->andWhere("p.id = :pas")
                ->setParameter('pas', $pasajero);
        }

        if ($desde != '') {
            $talones->andWhere('t.Numero >= :des')
                ->setParameter('des', $desde);
        }

        if ($hasta != '') {
            $talones->andWhere('t.Numero <= :has')
                ->setParameter('has', $hasta);
        }

        $talones = $talones->getQuery()
            ->getResult();

        return $talones;
    }

    public function talonesListFilter($offset, $limit, $status, $pasajero, $desde, $hasta, $rifa, $sorteo)
    {
        $talones = $this->createQueryBuilder('t')
            ->select(
                't.id, 
            t.Numero, 
            t.SorteoNumero, 
            t.FechaSorteo, 
            t.Estado, 
            t.Precio, 
            t.FechaRegistro, 

            r.id as rifa_id,
            r.nombre as rifa_nombre,

            s.id as sorteo_id,
            s.sorteoNumero as sorteo_numero,
            s.fechaSorteo as fecha_sorteo,
            s.valorTalon as valor_talon,

            p.id as PasId, 
            p.Estado as PasajeroEstado,
            p.Comentarios as PasajeroComentarios,

            u.Nombre as UniversidadNombre,

            i.Nombre as ItinerarioNombre,
            
            v.Nombre as ViajeNombre,
            
            per.id as PerId,
            per.Nombres, 
            per.Apellidos, 
            per.Cedula,
            per.Celular,

            d.id as DepId,
            d.Monto,
            d.Tipo,
            d.Fecha,
            d.Comentario as DepositoComentario,

            c.id as CompradorId,
            c.Nombre as CompradorNombre, 
            c.Email as CompradorEmail, 
            c.Celular as CompradorCelular, 
            c.Departamento as CompradorDepartamento'
            )
            ->leftJoin('t.Pasajero', 'p')
            ->leftJoin('t.sorteo', 's')
            ->leftJoin('s.rifa', 'r')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('t.Comprador', 'c')
            ->leftJoin('p.Universidad', 'u')
            ->leftJoin('p.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->leftJoin('t.Deposito', 'd')
            ->orderBy('t.Numero, r.id, s.sorteoNumero', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($rifa != 'todos') {
            $talones->andWhere("r.id = :rifa_id")
                ->setParameter('rifa_id', $rifa);
        }

        if ($sorteo != 'todos') {
            $talones->andWhere("s.id = :sorteo_id")
                ->setParameter('sorteo_id', $sorteo);
        }

        if ($status != 'todos') {
            $talones->andWhere("t.Estado = :status")
                ->setParameter('status', $status);
        }

        if ($pasajero != 'todos' && $pasajero != '') {
            $talones->andWhere("p.id = :pas")
                ->setParameter('pas', $pasajero);
        }

        if ($desde != '') {
            $talones->andWhere('t.Numero >= :des')
                ->setParameter('des', $desde);
        }

        if ($hasta != '') {
            $talones->andWhere('t.Numero <= :has')
                ->setParameter('has', $hasta);
        }

        $talones = $talones->getQuery()
            ->getResult();

        return $talones;
    }

    // SEIJI listado nuevo de talones
    public function talonesListBetweenNumber($desde, $hasta)
    {
        $talones = $this->createQueryBuilder('t')
            ->select(
                't.id, 
            t.Numero, 
            t.SorteoNumero, 
            t.FechaSorteo, 
            t.Estado, 
            t.Precio, 
            t.FechaRegistro, 

            r.id as rifa_id,
            r.nombre as rifa_nombre,

            s.id as sorteo_id,
            s.sorteoNumero as sorteo_numero,
            s.fechaSorteo as fecha_sorteo,
            s.valorTalon as valor_talon,

            p.id as PasId, 
            p.Estado as PasajeroEstado,
            p.Comentarios as PasajeroComentarios,

            u.Nombre as UniversidadNombre,

            i.Nombre as ItinerarioNombre,
            
            v.Nombre as ViajeNombre,
            
            per.id as PerId,
            per.Nombres, 
            per.Apellidos, 
            per.Cedula,
            per.Celular,

            d.id as DepId,
            d.Monto,
            d.Tipo,
            d.Fecha,
            d.Comentario as DepositoComentario,

            c.id as CompradorId,
            c.Nombre as CompradorNombre, 
            c.Email as CompradorEmail, 
            c.Celular as CompradorCelular, 
            c.Departamento as CompradorDepartamento'
            )
            ->leftJoin('t.Pasajero', 'p')
            ->leftJoin('t.sorteo', 's')
            ->leftJoin('s.rifa', 'r')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('t.Comprador', 'c')
            ->leftJoin('p.Universidad', 'u')
            ->leftJoin('p.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->leftJoin('t.Deposito', 'd')
            ->andWhere('t.Numero BETWEEN :desde AND :hasta')
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->orderBy('t.Numero, r.id, s.sorteoNumero', 'ASC')
            ->getQuery()
            ->getResult();
        return $talones;
    }

    /*public function talonesByDeposito($idDeposito)
    {
        $talones = $this->createQueryBuilder('t')
            ->select('t.id, t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, c.Nombre,
         c.Email, c.Celular, SUM(t.valor) as total_registrado, SUM(t.Recaudacion) as total_recaudado')
            ->leftJoin('t.Comprador', 'c')
            ->where("t.Deposito = :dep")
            ->setParameter('dep', 10514)
            ->getQuery()
            ->getResult();
        return $talones;
    }*/

    public function talonesByDeposito($deposito)
    {
        $talones = $this->createQueryBuilder('t')
            ->select('t.id, t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, c.Nombre,
         c.Email, c.Celular, SUM(t.valor) as total_registrado, SUM(t.Recaudacion) as total_recaudado')
            ->leftJoin('t.Comprador', 'c')
            ->where("t.Deposito = :dep")
            ->setParameter('dep', $deposito)
            ->getQuery()
            ->getResult();
        return $talones;
    }

    /* public function talonesByNumeroRifa($numeroRifa)
    {
        $talones = $this->createQueryBuilder('t')
            ->select('t.id, t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, c.Nombre, c.Email, c.Celular')
            ->leftJoin('t.Comprador', 'c')
            ->where("t.Deposito = :dep")
            ->setParameter('dep', $numeroRifa)
            ->getQuery()
            ->getResult();

        return $talones;
    }*/

    public function findBetweenNumero($desde, $hasta, $sorteo)
    {
        return $this->createQueryBuilder('t')
            ->select('t.id, t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, 
            per.Nombres as nombres, per.Apellidos as apellidos')
            ->leftJoin('t.Pasajero', 'pas')
            ->leftJoin('pas.Persona', 'per')
            ->andWhere('t.sorteo = :sorteo')
            ->setParameter('sorteo', $sorteo)
            ->andWhere('t.Numero BETWEEN :desde AND :hasta')
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->orderBy('t.id', 'ASC')
            ->distinct('t.id')
            ->getQuery()
            ->getResult();
    }

    public function updateTalonesByIdSorteo($sorteo, $fecha, $valor, $recaudacion)
    {
        /* $talones = $this->createQueryBuilder('t')
            ->update('t.id, t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, c.Nombre,
         c.Email, c.Celular, SUM(t.valor) as total_registrado, SUM(t.Recaudacion) as total_recaudado')
            ->leftJoin('t.Comprador', 'c')
            ->where("t.Deposito = :dep")
            ->setParameter('dep', $deposito)
            ->execute();
        return $talones;*/

        $queryBuilder = $this->createQueryBuilder('t')
            ->update()
            ->set('t.FechaSorteo', ':fecha')
            ->set('t.Precio', ':valor')
            ->set('t.valor', ':valor')
            ->set('t.Recaudacion', ':recaudacion')
            ->where('t.sorteo = :sorteo')
            ->setParameter('fecha', $fecha)
            ->setParameter('valor', $valor)
            ->setParameter('recaudacion', $recaudacion)
            ->setParameter('sorteo', $sorteo);

        $query = $queryBuilder->getQuery();
        $query->execute();
    }

    public function talonesListBolsa($pasajero)
    {

        $fecha_actual_aux = date("Y-m-d H:i:s");

        $result = $this->createQueryBuilder('t')
            ->select(
                't.id,
            t.Numero, 
            t.Estado,
            t.SorteoNumero,

            per.id as PerId,
            per.Nombres, 
            per.Apellidos,
            
            p.id as pasajero_id,

            r.id as rifa_id,
            r.nombre as rifa_nombre,

            s.id as sorteo_id,
            s.sorteoNumero as sorteo_numero,
            s.fechaSorteo as fecha_sorteo,
            s.valorTalon as valor_talon
            '
            )
            ->leftJoin('t.Pasajero', 'p')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('t.sorteo', 's')
            ->leftJoin('s.rifa', 'r')
            ->where("t.Pasajero <> :pas AND t.Estado = 'Para transferir' AND s.fechaSorteo > '" . $fecha_actual_aux . "'")
            ->setParameter('pas', $pasajero)
            ->orderBy('t.Numero, r.id, s.sorteoNumero', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
