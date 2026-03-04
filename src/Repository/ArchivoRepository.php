<?php

namespace App\Repository;

use App\Entity\Archivo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Archivo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Archivo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Archivo[]    findAll()
 * @method Archivo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArchivoRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Archivo::class);
        $this->manager = $entityManager;
    }


    public function save($documento, $nombre, $tipo, $url)
    {
        $archivo = new Archivo();

        $archivo
            ->setDocumento($documento)
            ->setNombre($nombre)
            ->setTipo($tipo)
            ->setUrl($url)
            ->setFechaCreado(new \DateTime('now'));

        $this->manager->persist($archivo);
        $this->manager->flush();

        return $archivo;
    }

    // /**
    //  * @return Archivo[] Returns an array of Archivo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Archivo
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
