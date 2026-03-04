<?php

namespace App\Repository;

use App\Entity\LinkPagoRifaTalones;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method LinkPagoRifaTalones|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkPagoRifaTalones|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkPagoRifaTalones[]    findAll()
 * @method LinkPagoRifaTalones[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkPagoRifaTalonesRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, LinkPagoRifaTalones::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return LinkPagoRifaTalones[] Returns an array of LinkPagoRifaTalones objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LinkPagoRifaTalones
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function talonesByLinkPago($link)
    {
        $talones = $this->createQueryBuilder('lt')
            ->select('lt.id as idRelacion, t.id as idTalon , t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, c.Nombre, c.Email, c.Celular')
            ->leftJoin('lt.Talon', 't')
            ->leftJoin('t.Comprador', 'c')
            ->where("lt.LinkPagoRifa = :lin")
            ->setParameter('lin', $link)
            ->getQuery()
            ->getResult();

        return $talones;
    }

    public function talonesPendientesByLinkPago($link)
    {
        $talones = $this->createQueryBuilder('lt')
            ->select('lt.id as idRelacion, t.id as idTalon , t.Numero, t.Precio, t.Estado, t.SorteoNumero, t.FechaSorteo, c.Nombre, c.Email, c.Celular')
            ->leftJoin('lt.Talon', 't')
            ->leftJoin('t.Comprador', 'c')
            ->where("lt.LinkPagoRifa = :lin AND t.Estado = :estado")
            ->setParameter('lin', $link)
            ->setParameter('estado', 'Pendiente de Pago')
            ->getQuery()
            ->getResult();

        return $talones;
    }

    public function linksPendientesWithTalonesPendientes($desde, $limit)
    {
        $talones = $this->createQueryBuilder('lt')
            ->select('l.id AS id, l.Estado as estado, d.id AS idDeposito, d.Tipo AS tipo, pas.id AS idPasajero, per.Nombres AS nombrePasajero,
         per.Apellidos AS apellidoPasajero, l.CompradorNombre AS nombreComprador, l.CompradorApellido AS apellidoComprador, l.CompradorEmail AS compradorEmail, l.CompradorCelular AS compradorCelular')
            ->leftJoin('lt.Talon', 't')
            ->leftJoin('lt.LinkPagoRifa', 'l')
            ->leftJoin('t.Deposito', 'd')
            ->leftJoin('l.Pasajero', 'pas')
            ->leftJoin('pas.Persona', 'per')
            ->where("l.Estado = :estado AND t.Estado = :estado")
            ->groupBy('l.id')
            ->setParameter('estado', 'Pendiente de Pago')
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $talones;
    }

    public function saveLinkPagoRifaTalones($link, $talon)
    {
        $linkTal = new LinkpagorifaTalones();
        $linkTal->setLinkPagoRifa($link);
        $linkTal->setTalon($talon);

        $this->manager->persist($linkTal);
        $this->manager->flush();

        return $linkTal;
    }
}
