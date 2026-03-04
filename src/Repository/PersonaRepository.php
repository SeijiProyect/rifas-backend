<?php

namespace App\Repository;

use DateTimeImmutable;
use App\Entity\Persona;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Persona|null find($id, $lockMode = null, $lockVersion = null)
 * @method Persona|null findOneBy(array $criteria, array $orderBy = null)
 * @method Persona[]    findAll()
 * @method Persona[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonaRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Persona::class);
        $this->manager = $entityManager;
    }

    public function responsePersona(Persona $persona)
    {
        $personaResponse = array(
            'id' => $persona->getId(),
            'Nombres' => $persona->getNombres(),
            'Apellidos' => $persona->getApellidos(),
            'FechaNacimiento' => $persona->getFechaNacimiento(),
            'Direccion' => $persona->getDireccion(),
            'Cedula' => $persona->getCedula(),
            'Celular' => $persona->getCelular(),
            'Sexo' => $persona->getSexo()
        );

        return $personaResponse;
    }

    public function save(Persona $persona)
    {
        $dateAux = date('Y-m-d H:i:s');
        $date_fecha_actual = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateAux);
        $new_persona = new Persona();
        $new_persona
            ->setNombres($persona->getNombres())
            ->setApellidos($persona->getApellidos())
            ->setFechaNacimiento($persona->getFechaNacimiento())
            ->setDireccion($persona->getDireccion())
            ->setCedula($persona->getCedula())
            ->setCelular($persona->getCelular())
            ->setSexo($persona->getSexo())
            ->setCreatedAt($date_fecha_actual);

        $this->manager->persist($new_persona);
        $this->manager->flush();

        return $new_persona;
    }

    public function updatePersona(Persona $persona)
    {
        $this->manager->persist($persona);
        $this->manager->flush();
        return $persona;
    }

    public function findByTermino($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('per')
            ->select(
                '
            per.Nombres,
            per.Apellidos,
            per.id as PerId,
            per.Cedula,
            per.Celular
            '
            )
            ->groupBy('per.id')
            ->orderBy('per.Apellidos')
            ->andWhere('per.Nombres LIKE :term OR per.Apellidos LIKE :term OR per.Cedula LIKE :term OR per.Celular LIKE :term OR per.id = :termEquals')
            ->setParameter('term', '%' . $termino . '%')
            ->setParameter('termEquals', $termino)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByEmail($termino, $desde, $limit)
    {
        return $this->createQueryBuilder('per')
            ->select(
                '
            per.Nombres,
            per.Apellidos,
            per.id as PerId,
            per.Cedula,
            per.Celular
            '
            )
            ->groupBy('per.id')
            ->orderBy('per.Apellidos')
            ->andWhere('per.id = :users')
            ->setParameter('users', $termino)
            ->distinct()
            // ->setFirstResult($desde)
            //->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBySexo($sexo, $desde, $limit)
    {
        return $this->createQueryBuilder('per')
            ->select(
                '
            per.Nombres,
            per.Apellidos,
            per.id as PerId,
            per.Cedula,
            per.Celular
            '
            )
            ->groupBy('per.id')
            ->orderBy('per.Apellidos')
            ->andWhere('per.Sexo = :termEquals')
            ->setParameter('termEquals', $sexo)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByFechas($fechaIni, $fechaFin, $desde, $limit)
    {
        return $this->createQueryBuilder('per')
            ->select(
                '
            per.Nombres,
            per.Apellidos,
            per.id as PerId,
            per.Cedula,
            per.Celular
            '
            )
            ->groupBy('per.id')
            ->orderBy('per.Apellidos')
            ->andWhere('per.FechaNacimiento >= :fechaIniEquals AND per.FechaNacimiento <= :fechaFinEquals')
            ->setParameter('fechaIniEquals', $fechaIni)
            ->setParameter('fechaFinEquals', $fechaFin)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByFechasSexo($fechaIni, $fechaFin, $sexo, $desde, $limit)
    {
        return $this->createQueryBuilder('per')
            ->select(
                '
            per.Nombres,
            per.Apellidos,
            per.id as PerId,
            per.Cedula,
            per.Celular
            '
            )
            ->groupBy('per.id')
            ->orderBy('per.Apellidos')
            ->andWhere('per.FechaNacimiento >= :fechaIniEquals AND per.FechaNacimiento <= :fechaFinEquals AND per.Sexo = :sexoEquals')
            ->setParameter('fechaIniEquals', $fechaIni)
            ->setParameter('fechaFinEquals', $fechaFin)
            ->setParameter('sexoEquals', $sexo)
            ->setFirstResult($desde)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Persona[] Returns an array of Persona objects
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
    public function findOneBySomeField($value): ?Persona
    {
    return $this->createQueryBuilder('p')
    ->andWhere('p.exampleField = :val')
    ->setParameter('val', $value)
    ->getQuery()
    ->getOneOrNullResult()
    ;
    }
    */

    public function getPersonaById($id)
    {
        $persona = $this->find(array("id" => $id));
        if ($persona) {
            return $persona;
        } else {
            return null;
        }
    }
}
