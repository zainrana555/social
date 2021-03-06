<?php

namespace App\Repository;

use App\Entity\Friend;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    /**
     * @param User $user
     * @return Friend[] Returns an array of Friend objects
     */
    public function getFriendRequests(User $user)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.friendUser = :user')
            ->andWhere('f.accepted = false')
            ->setParameter('user', $user)
            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @param User $user
//     * @return Friend[] Returns an array of Friend objects
//     */
//    public function getUserFriends(User $user)
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.friendUser = :user')
//            ->orWhere('f.user = :user')
//            ->andWhere('f.accepted = true')
//            ->setParameter('user', $user)
//            ->orderBy('f.id', 'ASC')
////            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//            ;
//    }

    /*
    public function findOneBySomeField($value): ?Friend
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
