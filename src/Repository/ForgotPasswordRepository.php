<?php

namespace App\Repository;

use App\Entity\ForgotPassword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ForgotPassword|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForgotPassword|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForgotPassword[]    findAll()
 * @method ForgotPassword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForgotPasswordRepository extends ServiceEntityRepository
{
    private $manager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, ForgotPassword::class);
        $this->manager = $entityManager;
    }

    // /**
    //  * @return ForgotPassword[] Returns an array of ForgotPassword objects
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
    public function findOneBySomeField($value): ?ForgotPassword
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function save($user, $token, $expire)
    {
        $fp = new ForgotPassword();

        $fp
        ->setUser($user)
        ->setToken($token)
        ->setExpire($expire)
        ->setCreatedAt(new \DateTimeImmutable('now'));

        $this->manager->persist($fp);
        $this->manager->flush();

        return $fp;
    }
    
    public function update(ForgotPassword $forgotPassword)
    {
        $forgotPassword->setUpdatedAt(new \DateTimeImmutable('now'));
        $this->manager->persist($forgotPassword);
        $this->manager->flush();

        return $forgotPassword;
    }
    
    public function delete(ForgotPassword $forgotPassword)
    {
        $this->manager->remove($forgotPassword);
        $this->manager->flush();
    }
}
