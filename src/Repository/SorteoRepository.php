<?php

namespace App\Repository;

use App\Entity\Sorteo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sorteo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sorteo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sorteo[]    findAll()
 * @method Sorteo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SorteoRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Sorteo::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return Sorteo[] Returns an array of Sorteo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sorteo
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function deleteSorteo(Sorteo $sorteo)
    {
        $this->manager->remove($sorteo);
        $this->manager->flush();
    }

    public function updateSorteo(Sorteo $sorteo)
    {
        $this->manager->persist($sorteo);
        $this->manager->flush();
        return $sorteo;
    }

    public function getSorteoById($id)
    {
        $sorteo= $this->find(array("id" => $id));
        if ($sorteo) {
            return $sorteo;
        } else {
            return null;
        }
    }

    public function saveSorteo(Sorteo $sorteo)
    {
        $new_sorteo = new Sorteo();
        $new_sorteo
            ->setRifa($sorteo->getRifa())
            ->setSorteoNumero($sorteo->getSorteoNumero())
            ->setNumeroInicialTalon($sorteo->getNumeroInicialTalon())
            ->setNumeroFinalTalon($sorteo->getNumeroFinalTalon())
            ->setFechaSorteo($sorteo->getFechaSorteo())
            ->setLugar($sorteo->getLugar())
            ->setValorTalon($sorteo->getValorTalon())
            ->setPorcentajePremio($sorteo->getPorcentajePremio());

        $this->manager->persist($new_sorteo);
        $this->manager->flush();

        return $new_sorteo;
    }

    public function sorteosByRifa($rifa)
    {     
        $sorteos = $this->createQueryBuilder('s')
        ->select('s.id,s.fechaSorteo, s.sorteoNumero, r.id as rifa_id, r.nombre as rifa_nombre, 
        s.numeroInicialTalon, s.numeroFinalTalon')
        ->leftJoin('s.rifa', 'r')
        ->where("s.rifa = :rifa")
        ->setParameter('rifa', $rifa)
        ->getQuery()
        ->getResult();

        return $sorteos;
    }
}
