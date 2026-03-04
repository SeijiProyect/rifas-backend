<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

use App\Security\JwtAuthenticator;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    private $manager;
    private $jwtAuthenticator;

    public function __construct(ManagerRegistry $registry, JwtAuthenticator $jwtAuthenticator, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, User::class);

        $this->manager = $entityManager;
        // $this->userRepository = $userRepository;
        $this->jwtAuthenticator = $jwtAuthenticator;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function update(User $user)
    {
        $this->manager->persist($user);
        $this->manager->flush();
        return $user;
    }


    public function save(User $user)
    {
        $new_user = new User();
        $new_user
            ->setEmail($user->getEmail())
            ->setPassword($user->getPassword())
            ->setRoles($user->getRoles())
            ->setPersona($user->getPersona());
        $this->manager->persist($new_user);
        $this->manager->flush();

        return $new_user;
    }

    public function getUserIdByToken(string $token)
    {
        $user = $this->jwtAuthenticator->getUserEmail($token);

        if ($user) {
            $user = $this
                ->findOneBy([
                    'email' => $user,
                ]);

            return $user;
        } else {
            return false;
        }
    }
}
