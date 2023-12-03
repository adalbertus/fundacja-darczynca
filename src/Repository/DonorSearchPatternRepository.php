<?php

namespace App\Repository;

use App\Entity\DonorSearchPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DonorSearchPattern>
 *
 * @method DonorSearchPattern|null find($id, $lockMode = null, $lockVersion = null)
 * @method DonorSearchPattern|null findOneBy(array $criteria, array $orderBy = null)
 * @method DonorSearchPattern[]    findAll()
 * @method DonorSearchPattern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonorSearchPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DonorSearchPattern::class);
    }

//    /**
//     * @return DonorSearchPattern[] Returns an array of DonorSearchPattern objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DonorSearchPattern
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
