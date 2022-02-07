<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
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

    /**
     * @param User $user
     * @return User[] Returns an array of User objects
     */
    public function getUserFriends(User $user)
    {
        $collection1 = $this->createQueryBuilder('u')
            ->innerJoin('u.friends', 'f')
            ->where('f.friendUser = :user')
            ->andWhere('f.accepted = true')
            ->setParameter('user', $user)
            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;

        $collection2 = $this->createQueryBuilder('u')
            ->innerJoin('u.friendsWith', 'fw')
            ->where('fw.user = :user')
            ->andWhere('fw.accepted = true')
            ->setParameter('user', $user)
            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;

        return array_merge($collection1, $collection2);
    }

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
}
