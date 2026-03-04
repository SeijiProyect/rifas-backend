<?php

namespace App\Repository;

use App\Entity\Comprador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Comprador|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comprador|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comprador[]    findAll()
 * @method Comprador[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompradorRepository extends ServiceEntityRepository
{
    private $manager;
    private $saveCompradorEmail = true;
    private $saveCompradorCelular = true;
    private $saveCompradorDepartamento = true;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Comprador::class);
        $this->manager = $entityManager;
    }

    public function getCompradorById($id)
    {
        $comprador = $this->find(array("id" => $id));
        if ($comprador) {
            return $comprador;
        } else {
            return null;
        }
    }

    public function saveComprador($nombre, $email, $celular, $departamento)
    {
        $comprador = new Comprador();

        $comprador
            ->setCreatedAt(new \DateTimeImmutable('now'))
            ->setNombre($nombre);

        if ($this->saveCompradorEmail) {
            $comprador->setEmail($email);
        }

        if ($this->saveCompradorCelular) {
            $comprador->setCelular($celular);
        }

        if ($this->saveCompradorDepartamento) {
            $comprador->setDepartamento($departamento);
        }


        $this->manager->persist($comprador);
        $this->manager->flush();

        return $comprador;
    }
}
