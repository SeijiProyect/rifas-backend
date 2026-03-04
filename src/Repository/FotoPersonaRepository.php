<?php

namespace App\Repository;

use App\Entity\FotoPersona;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FotoPersona|null find($id, $lockMode = null, $lockVersion = null)
 * @method FotoPersona|null findOneBy(array $criteria, array $orderBy = null)
 * @method FotoPersona[]    findAll()
 * @method FotoPersona[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FotoPersonaRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, FotoPersona::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return FotoPersona[] Returns an array of FotoPersona objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FotoPersona
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function saveFoto($persona, $nombre, $url)
    {
        $foto = new FotoPersona();

        $foto
            ->setPersona($persona)
            ->setNombre($nombre)
            ->setUrl($url)
            ->setFecha(new \DateTime('now'));

        $this->manager->persist($foto);
        $this->manager->flush();

        return $foto;
    }

    public function findByPersonaUltimaFoto($persona)
    {
        return $this->createQueryBuilder('f')
        ->select(
            '
        f.id,
        f.url,
        f.nombre
        '
        )
            ->andWhere('f.persona = :val')
            ->setParameter('val', $persona)
            ->orderBy('f.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }
    
}
