<?php

namespace App\Repository;

use App\Entity\Deposito;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Deposito|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deposito|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deposito[]    findAll()
 * @method Deposito[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepositoRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Deposito::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return Deposito[] Returns an array of Deposito objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Deposito
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function saveDeposito($pasajero, $monto, $tipo, $fecha, $csv, $comentario, $geopay = false)
    {
        $deposito = new Deposito();

        $deposito
        ->setPasajero($pasajero)
        ->setMonto($monto)
        ->setTipo($tipo)
        ->setFecha($fecha)
        ->setFechaProcesado(new \DateTime('now'))
        ->setCreatedAt(new \DateTimeImmutable('now'))
        ->setCsv($csv)
        ->setComentario($comentario)
        ->setGeopay($geopay);

        $this->manager->persist($deposito);
        $this->manager->flush();

        return $deposito;
    }

    public function updateDeposito(Deposito $deposito): Deposito
    {
        $deposito->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->manager->persist($deposito);
        $this->manager->flush();

        return $deposito;
    }

    public function search($pStr)
    {
        $result = $this->createQueryBuilder('d')
            ->select(
                'd.id, 
                d.Monto, 
                d.Tipo, 
                d.Fecha, 
                d.FechaProcesado, 
                d.Comentario, 
                
                p.id as PasId, 
                p.Estado as PasajeroEstado,
                p.Comentarios as PasajeroComentarios,

                u.Nombre as UniversidadNombre,

                i.Nombre as ItinerarioNombre,
                
                v.Nombre as ViajeNombre,
                
                per.id as PerId,
                per.Nombres, 
                per.Apellidos, 
                per.Cedula
                '
            )
            ->leftJoin('d.Pasajero', 'p')
            ->leftJoin('p.Persona', 'per')
            ->leftJoin('p.Universidad', 'u')
            ->leftJoin('p.Itinerario', 'i')
            ->leftJoin('i.Viaje', 'v')
            ->orderBy('d.Fecha')
            ->andWhere('d.monto LIKE :term OR per.Nombres LIKE :term OR per.Apellidos LIKE :term OR per.Cedula LIKE :term OR per.Celular LIKE :term')
            ->setParameter('term', '%' . $pStr . '%')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function depositosByPasajero($pasajero)
    {     
        $depositos = $this->createQueryBuilder('d')
        ->select('d.id,d.Fecha, d.Tipo, d.Monto ')
        ->leftJoin('d.Pasajero', 'p')
        ->where("d.Pasajero = :pas")
        ->setParameter('pas', $pasajero)
        ->getQuery()
        ->getResult();

        return $depositos;
    }


}
