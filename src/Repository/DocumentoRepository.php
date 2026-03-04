<?php

namespace App\Repository;

use App\Entity\Documento;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Documento|null find($id, $lockMode = null, $lockVersion = null)
 * @method Documento|null findOneBy(array $criteria, array $orderBy = null)
 * @method Documento[]    findAll()
 * @method Documento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentoRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Documento::class);
        $this->manager = $entityManager;
    }

    public function findByPersonaDocumento($persona)
    {
        return $this->createQueryBuilder('f')
            ->select('f.id, f.numero, t.nombre as tipo, p.nombre as pais, f.serie, f.fecha_expedicion, f.fecha_vencimiento, f.imagen_url')
            ->leftJoin('f.tipoDocumento', 't')
            ->leftJoin('f.pais', 'p')
            ->where('f.persona = :val')
            ->setParameter('val', $persona)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findByPasajeroDocumento($pasajero)
    {
        return $this->createQueryBuilder('f')
            ->select('f.id, f.numero, t.nombre as tipo, p.nombre as pais, f.serie, f.fecha_expedicion, f.fecha_vencimiento, f.imagen_url')
            ->leftJoin('f.tipoDocumento', 't')
            ->leftJoin('f.pais', 'p')
            ->where('f.persona = :val')
            ->setParameter('val', $pasajero)
            ->getQuery()
            ->getResult();
    }*/

    public function save($tipo, $pais, $persona, $numero, $fecha_exp, $fecha_ven)
    {
        $doc = new Documento();

        $doc
            ->setPais($pais)
            ->setPersona($persona)
            ->setTipoDocumento($tipo)
            ->setNumero($numero)
            ->setFechaExpedicion($fecha_exp)
            ->setFechaVencimiento($fecha_ven);
            
        $this->manager->persist($doc);
        $this->manager->flush();

        return $doc;
    }

    // /**
    //  * @return Documento[] Returns an array of Documento objects
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
    public function findOneBySomeField($value): ?Documento
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
