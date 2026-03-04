<?php

namespace App\Repository;

use App\Entity\LinkPagoRifaSeleccion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method LinkPagoRifaSeleccion|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkPagoRifaSeleccion|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkPagoRifaSeleccion[]    findAll()
 * @method LinkPagoRifaSeleccion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkPagoRifaSeleccionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, LinkPagoRifaSeleccion::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return LinkPagoRifaSeleccion[] Returns an array of LinkPagoRifaSeleccion objects
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
    public function findOneBySomeField($value): ?LinkPagoRifaSeleccion
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function saveLinkPagoRifaSeleccion($link, $tarjeta, $tipotarjeta, $cuotas)
    {
        $linkPS = new LinkPagoRifaSeleccion();

        $linkPS->setLinkPagoRifa($link);
        $linkPS->setTarjeta($tarjeta);
        $linkPS->setTipoTarjeta($tipotarjeta);
        $linkPS->setCuotas($cuotas);
        
        $linkPS->setCreatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($linkPS);
        $this->manager->flush();

        return $link;
    }
}
