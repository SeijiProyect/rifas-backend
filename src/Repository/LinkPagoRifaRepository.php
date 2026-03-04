<?php

namespace App\Repository;

use App\Entity\LinkPagoRifa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Deposito;

/**
 * @method LinkPagoRifa|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkPagoRifa|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkPagoRifa[]    findAll()
 * @method LinkPagoRifa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkPagoRifaRepository extends ServiceEntityRepository
{
    private $manager;
    private $saveCompradorEmail = true;
    private $saveCompradorCelular = true;
    private $saveCompradorDepartamento = true;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, LinkPagoRifa::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return LinkPagoRifa[] Returns an array of LinkPagoRifa objects
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
    public function findOneBySomeField($value): ?LinkPagoRifa
    {
    return $this->createQueryBuilder('l')
    ->andWhere('l.exampleField = :val')
    ->setParameter('val', $value)
    ->getQuery()
    ->getOneOrNullResult()
    ;
    }
    */

    public function findBetweenRegister($desde, $hasta)
    {
        return $this->createQueryBuilder('l')
            ->where('l.id BETWEEN :desde AND :hasta')
            ->setParameter('desde', $desde)
            ->setParameter('hasta', $hasta)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByNumeroRifa($numero, $desde, $limit)
    {

        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT l.id, l.comprador_nombre as nombreComprador, l.comprador_apellido as apellidoComprador, l.comprador_email as
        compradorEmail, l.comprador_celular as compradorCelular, l.estado as estado,
        Pas.id as idPasajero, Per.nombres as nombrePasajero, Per.apellidos as apellidoPasajero, D.id as idDeposito, D.tipo as formaPago
        from link_pago_rifa_talones as lt
        LEFT JOIN link_pago_rifa l ON l.id = lt.link_pago_rifa_id
        LEFT JOIN talon t ON t.id = lt.talon_id
        LEFT JOIN deposito D ON D.id = l.deposito_id
        LEFT JOIN pasajero Pas ON Pas.id = l.pasajero_id
        LEFT JOIN persona Per ON Per.id = Pas.persona_id
        where t.numero = ' . $numero . '
        GROUP BY l.id,D.id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    public function findByTermino($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('l')
            ->where('l.CompradorNombre LIKE :term OR l.CompradorApellido LIKE :term OR per.Apellidos LIKE :term OR per.Nombres LIKE :term OR l.id = :termEqual')
            ->leftJoin('l.Pasajero', 'p')
            ->leftJoin('p.Persona', 'per')
            ->setParameter('term', '%' . $termino . '%')
            ->setParameter('termEqual', $termino)
            ->orderBy('l.id', 'ASC')
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByEstado($estado, $desde, $limit)
    {
        return $this->createQueryBuilder('l')
            ->where('l.Estado = :es')
            ->setParameter('es', $estado)
            ->orderBy('l.id', 'ASC')
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTerminoAndEstado($termino, $estado, $desde, $limit)
    {
        return $this->createQueryBuilder('l')
            ->where('l.CompradorNombre LIKE :term OR l.CompradorApellido LIKE :term OR per.Apellidos LIKE :term OR per.Nombres LIKE :term')
            ->leftJoin('l.Pasajero', 'p')
            ->leftJoin('p.Persona', 'per')
            ->setParameter('term', '%' . $termino . '%')
            ->orderBy('l.id', 'ASC')
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->andWhere("l.Estado = :es")
            ->setParameter('es', $estado)
            ->getQuery()
            ->getResult()
        ;
    }

    public function saveLinkPagoRifa($pasajero, $compradorMail, $compradorName, $compradorLastName, $compradorCelular, $compradorDepartment, $status, $encryptedLink, $asumirRecargo)
    {
        $link = new LinkPagoRifa();

        $link->setPasajero($pasajero);
        $link->setCompradorNombre($compradorName);
        $link->setCompradorApellido($compradorLastName);

        if ($this->saveCompradorEmail) {
            $link->setCompradorEmail($compradorMail);
        } else {
            $link->setCompradorEmail('');
        }

        if ($this->saveCompradorCelular) {
            $link->setCompradorCelular(trim(str_replace(' ', '', $compradorCelular)));
        } else {
            $link->setCompradorCelular('');
        }

        if ($this->saveCompradorDepartamento) {
            $link->setCompradorDepartamento($compradorDepartment);
        } else {
            $link->setCompradorDepartamento('');
        }

        $link->setEstado($status);
        $link->setEncryptedLink($encryptedLink);
        $link->setCreatedAt(new \DateTimeImmutable('now'));
        $link->setAsumirrecargo($asumirRecargo);

        $this->manager->persist($link);
        $this->manager->flush();

        return $link;
    }

    public function updateLinkPagoRifa(LinkPagoRifa $link): LinkPagoRifa
    {
        $link->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->manager->persist($link);
        $this->manager->flush();

        return $link;
    }

    public function responseLinkPagoRifa(LinkPagoRifa $linkPagoRifa)
    {
        $pasajero = $linkPagoRifa->getPasajero() != null ? $linkPagoRifa->getPasajero()->getId() : null;
        $deposito = $linkPagoRifa->getDeposito() != null ? $linkPagoRifa->getDeposito()->getId() : null;

        $linkPagoRifaResponse = array(
            'id' => $linkPagoRifa->getId(),
            'Pasajero' => $pasajero,
            'Deposito' => $deposito,
            'CompradorNombre' => $linkPagoRifa->getCompradorNombre(),
            'CompradorApellido' => $linkPagoRifa->getCompradorApellido(),
            'CompradorEmail' => $linkPagoRifa->getCompradorEmail(),
            'CompradorCelular' => $linkPagoRifa->getCompradorCelular(),
            'CompradorDepartamento' => $linkPagoRifa->getCompradorDepartamento(),
            'Estado' => $linkPagoRifa->getEstado(),
            'EncryptedLink' => $linkPagoRifa->getEncryptedLink(),
            'GeocomToken' => $linkPagoRifa->getGeocomToken(),
            'AsumirRecargo' => $linkPagoRifa->getAsumirRecargo()
        );

        return $linkPagoRifaResponse;
    }
}