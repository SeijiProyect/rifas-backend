<?php

namespace App\Repository;

use App\Entity\PasajeroDocumento;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Proxies\__CG__\App\Entity\PasajeroServicio;

/**
 * @method PasajeroDocumento|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasajeroDocumento|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasajeroDocumento[]    findAll()
 * @method PasajeroDocumento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasajeroDocumentoRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, PasajeroDocumento::class);
        $this->manager = $entityManager; 
    }

    public function save($documento, $pasajero)
    {
        $pas_doc = new PasajeroDocumento();

        $pas_doc
            ->setDocumento($documento)
            ->setPasajero($pasajero)
            ->setFechaCreado(new \DateTime('now'));

        $this->manager->persist($pas_doc);
        $this->manager->flush();

        return $pas_doc;
    }

    public function findByPasajeroDocumento($pasajero)
    {
        return $this->createQueryBuilder('pd')
            ->select('f.id, f.numero, t.nombre as tipo, p.nombre as pais, f.serie, f.fecha_expedicion, f.fecha_vencimiento, f.imagen_url')
            ->leftJoin('pd.documento', 'f')
            ->leftJoin('pd.pasajero', 'pas')
            ->leftJoin('f.tipoDocumento', 't')
            ->leftJoin('f.pais', 'p')
            ->where('pd.pasajero = :val')
            ->setParameter('val', $pasajero)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return PasajeroDocumento[] Returns an array of PasajeroDocumento objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PasajeroDocumento
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
