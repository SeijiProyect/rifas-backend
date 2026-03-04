<?php

namespace App\Repository;

use App\Entity\Viaje;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Viaje|null find($id, $lockMode = null, $lockVersion = null)
 * @method Viaje|null findOneBy(array $criteria, array $orderBy = null)
 * @method Viaje[]    findAll()
 * @method Viaje[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViajeRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Viaje::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return Viaje[] Returns an array of Viaje objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Viaje
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function update(Viaje $viaje)
    {
        $this->manager->persist($viaje);
        $this->manager->flush();
        return $viaje;
    }


    public function viajesByPersona($persona)
    {
        $viajes = $this->createQueryBuilder('v')
            ->select('v.id, v.Numero, v.Precio, v.Estado')
            ->where("v.Deposito = :per")
            ->setParameter('per', $persona)
            ->getQuery()
            ->getResult();

        return $viajes;
    }

    public function viajesByPersonaNativeQuery($idPersona)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT p.id AS persona_id, pas.id AS pasajero_id, iti.id AS itinerario_id, iti.nombre AS itinerario_nombre,
        iti.fecha_inicio AS itinerario_fechaInicio, iti.fecha_fin AS itinerario_fechaFin, g.id AS grupo_id, 
        g.nombre AS grupo_nombre, v.id AS viaje_id, v.nombre AS viaje_nombre
        FROM persona p 
        INNER JOIN pasajero pas ON pas.persona_id = p.id
        INNER JOIN itinerario iti ON iti.id =  pas.itinerario_id
        INNER JOIN grupo g ON g.id =  iti.grupo_id
        INNER JOIN viaje v ON v.id =  iti.viaje_id
        WHERE p.id = '" . $idPersona . "'";

        $stmt = $conn->prepare($sql);
        //$stmt->bindParam(':id',$id);
        $resultSet = $stmt->executeQuery();

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative();
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
}
