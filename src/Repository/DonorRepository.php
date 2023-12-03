<?php

namespace App\Repository;

use App\Entity\Donor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Donor>
 *
 * @method Donor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Donor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Donor[]    findAll()
 * @method Donor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DonorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donor::class);
    }

    /**
     * QueryBuilder na potrzeby stronicowania
     *
     * @param  mixed $criteria
     * @return QueryBuilder
     */
    public function getPagerQueryBuilder(array $criteria = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.user', 'u');
        $this->_buildFilteringCriteria($qb, $criteria);
        return $qb;
    }

    private function _buildFilteringCriteria(QueryBuilder $qb, array $criteria): QueryBuilder
    {
        if (array_key_exists('details', $criteria)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'LOWER(d.name) LIKE :details',
                        'LOWER(u.email) LIKE :details',
                        'LOWER(u.firstName) LIKE :details',
                        'LOWER(u.lastName) LIKE :details',
                    )
                )
                ->setParameter('details', '%' . strtolower(trim($criteria['details'])) . '%');
        }

        if (array_key_exists('sorting', $criteria)) {
            switch ($criteria['sorting']) {
                case 'name-asc':
                    $qb->orderBy('d.name', 'ASC');
                    break;
                case 'name-desc':
                    $qb->orderBy('d.name', 'DESC');
                    break;
                default:
                    $qb->orderBy('d.name', 'DESC');
            }
        } else {
            $qb->orderBy('d.name', 'ASC');
        }

        return $qb;
    }

    /**
     * Usunięcie potencjalnie nieużywanych darczyńców, którzy zostali stworzeni
     * automatycznie (podczas importu CSV). Nieużywany to taki, który nie jest
     * przypisany do żadnego bank_history.
     *
     * @return void
     */
    public function deleteUnusedAutoCreated(): void
    {
        // dla czytelności usuwam w 2 krokach:
        // 1. pobieram ID darczyńców
        // 2. usuwam wszystkich darczyńców z wybranym ID

        // id darczyńców, którzy nie są przypisani do bank_history
        // SELECT d.id FROM donor d
        // LEFT JOIN bank_history bh ON (bh.donor_id = d.id)
        // WHERE d.is_auto = 1
        // GROUP BY d.id
        // HAVING COUNT(bh.id) = 0

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $result =
            $this->getEntityManager()->createNativeQuery('
        SELECT d.id FROM donor d
        LEFT JOIN bank_history bh ON (bh.donor_id = d.id)
        WHERE d.is_auto = :is_auto
        GROUP BY d.id
        HAVING COUNT(bh.id) = 0', $rsm)
                ->setParameter('is_auto', true)
                ->getScalarResult();
        $idList = array_map('current', $result);

        $this->getEntityManager()->createQuery('delete from ' . Donor::class . ' d where d in (:ids)')
            ->setParameter(':ids', $idList)
            ->execute();
    }

    //    /**
//     * @return Donor[] Returns an array of Donor objects
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

    //    public function findOneBySomeField($value): ?Donor
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
