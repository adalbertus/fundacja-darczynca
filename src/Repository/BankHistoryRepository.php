<?php

namespace App\Repository;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use App\Entity\Donor;
use App\Service\StringHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankHistory>
 *
 * @method BankHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BankHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BankHistory[]    findAll()
 * @method BankHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankHistory::class);
    }

    public function save(BankHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BankHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteAllDraft(): void
    {
        $q = $this->getEntityManager()->createQuery('delete from ' . BankHistory::class . ' bh where bh.is_draft = true');
        $q->execute();
    }

    public function findExistingMd5FromMd5List(array $md5List): array
    {
        $result = $this->getEntityManager()->createQuery('SELECT bh.md5 FROM ' . BankHistory::class . ' bh WHERE bh.md5 IN (:md5s)')
            ->setParameter('md5s', $md5List)
            ->getScalarResult();
        $result = array_map('current', $result);
        return $result;
    }

    /**
     * Pobranie wszystkich w statusie szkic
     */
    public function findAllDraft(): array
    {
        $result = $this->findBy(['is_draft' => true], ['date' => 'ASC']);
        return $result;
    }


    public function getLastUpdateDate(): mixed
    {
        $result = $this->createQueryBuilder('bh')
            ->select('MAX(bh.date)')
            ->where('bh.account = :account')
            ->andWhere('bh.is_draft = :is_draft')
            ->setParameter('is_draft', false)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    /**
     * Get Last rows of BankHistory based on criteria
     * @param array $criteria
     * @param string $joinWith - np. letter (jak chcemy pobrać od razu informacje o listach)
     * @param int $last
     * @return mixed
     */
    public function getLastHistory(array $criteria, string $joinWith = null, int $last = 5): mixed
    {
        $qb = $this->createQueryBuilder('bh')
            ->orderBy('bh.date', 'DESC');
        if (!StringHelper::isNullOrEmpty($joinWith)) {
            $qb->join("bh.$joinWith", $joinWith)
                ->addSelect($joinWith);
        }
        foreach ($criteria as $key => $value) {
            $qb->andWhere("bh.$key = :$key");
            $qb->setParameter(":$key", $value);
        }

        if ($last > 0) {
            $qb->setMaxResults($last);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Pobranie podsumowania:
     * - łączna kwota
     * - ostatnia aktualizacja
     *
     * @return array [
     *  'total' => value
     *  'updated' => date
     *  ]
     */
    public function getTotals(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('key', 'key');
        $rsm->addScalarResult('value', 'value');

        $query = $this->getEntityManager()->createNativeQuery("
        SELECT 'total' as `key`, IFNULL(SUM(bh.value), 0) AS value 
        FROM bank_history bh 
        WHERE bh.is_draft = :is_draft
        
        UNION

        SELECT 'updated' as `key`, IFNULL(MAX(bh.date), CURDATE()) AS value 
        FROM bank_history bh
        WHERE bh.is_draft = :is_draft 
        ", $rsm)
            ->setParameter('is_draft', false);
        $result = $query->getArrayResult();
        $summary['total'] = $result[0]['value'];
        $summary['updated'] = $result[1]['value'];
        return $summary;
    }

    /**
     * Podsumowanie wpłat darczyńcy:
     * - suma wszystkich wpłat
     * - suma wpłat poprzedniego roku
     * - suma wpłat bieżącego roku
     *
     * @param  mixed $donor
     * @return array ['total' => 0, 'prev_year' => 0, 'cur_year' => 0]
     */
    public function getTotalsForDonor(Donor $donor): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('key', 'key');
        $rsm->addScalarResult('value', 'value');

        $query = $this->getEntityManager()->createNativeQuery("
        SELECT 'total' as `key`, IFNULL(SUM(bh.value), 0) AS value 
        FROM bank_history bh 
        WHERE
            bh.is_draft = :is_draft
            AND
            bh.donor_id = :donor_id
        
        UNION

        SELECT 'prev_year' as `key`, IFNULL(SUM(bh.value), 0) AS value 
        FROM bank_history bh
        WHERE
            bh.is_draft = :is_draft
            AND
            bh.donor_id = :donor_id 
            AND
            YEAR(bh.date) = (YEAR(CURDATE()) - 1)
        
            UNION

        SELECT 'cur_year' as `key`, IFNULL(SUM(bh.value), 0) AS value 
        FROM bank_history bh
        WHERE
            bh.is_draft = :is_draft
            AND
            bh.donor_id = :donor_id 
            AND
            YEAR(bh.date) = YEAR(CURDATE())
                ", $rsm)
            ->setParameter('is_draft', false)
            ->setParameter('donor_id', $donor->getId());
        $result = $query->getArrayResult();
        $summary['total'] = $result[0]['value'];
        $summary['prev_year'] = $result[1]['value'];
        $summary['cur_year'] = $result[2]['value'];
        return $summary;
    }

    public function getPagerQueryBuilder(array $criteria): QueryBuilder
    {
        /** przykładowe zapytanie:
         * 
            SELECT
                bh.id AS id_0,
                bh.date AS date_1,
                bh.value AS value_2,
                bh.category AS category_3,
                bh.sub_category AS sub_category_4,
                bh.description AS description_6,
                bh.sender_name AS sender_name_7,
                bh.sender_bank_account AS sender_bank_account_8,
                bh.comment AS comment_9,
                bh.is_draft AS is_draft_10,
                bh.md5 AS md5_11,
                bh.raw AS raw_12,
                bh.manual AS manual_13,
                bh.flagged AS flagged_14,
                bh.created_at AS created_at_15,
                bh.updated_at AS updated_at_16
            FROM
                bank_history bh
                LEFT JOIN donor d ON bh.donor_id = d.id
            WHERE
                bh.is_draft = 0
                AND bh.category = 'brak'

            ORDER BY
            date_1 DESC

            LIMIT
            30
         */
        $qb = $this->createQueryBuilder('bh')
            ->leftJoin('bh.donor', 'd')
            ->where('bh.is_draft = :is_draft')
            ->setParameter('is_draft', false);
        $this->buildFilteringCriteria($qb, $criteria);
        return $qb;
    }

    private function buildFilteringCriteria(QueryBuilder $qb, array $criteria): void
    {
        if (array_key_exists('category', $criteria)) {
            $qb->andWhere('bh.category = :category')->setParameter(':category', $criteria['category']);
        }
        if (array_key_exists('flagged', $criteria)) {
            if ($criteria['flagged'] === 'true') {
                $qb->andWhere('bh.flagged = :flagged')->setParameter(':flagged', true);
            }
        }
        if (array_key_exists('subcategory', $criteria)) {
            $qb->andWhere('bh.sub_category = :subcategory')->setParameter(':subcategory', $criteria['subcategory']);
        }
        if (array_key_exists('description', $criteria)) {
            $qb->andWhere('LOWER(bh.description) LIKE :description')->setParameter(':description', '%' . strtolower($criteria['description']) . '%');
        }
        if (array_key_exists('details', $criteria)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'LOWER(bh.description) LIKE :details',
                        'LOWER(bh.comment) LIKE :details',
                        'LOWER(bh.sender_name) LIKE :details',
                        'LOWER(d.name) LIKE :details',
                    )
                )
                ->setParameter('details', '%' . strtolower(trim($criteria['details'])) . '%');
        }

        if (array_key_exists('valueFrom', $criteria)) {
            $qb->andWhere('bh.value >= :valueFrom')->setParameter(':valueFrom', $criteria['valueFrom']);
        }

        if (array_key_exists('valueTo', $criteria)) {
            $qb->andWhere('bh.value <= :valueTo')->setParameter(':valueTo', $criteria['valueTo']);
        }

        if (array_key_exists('startDate', $criteria)) {
            $qb->andWhere('bh.date >= :startDate')->setParameter(':startDate', $criteria['startDate']);
        }

        if (array_key_exists('endDate', $criteria)) {
            $qb->andWhere('bh.date <= :endDate')->setParameter(':endDate', $criteria['endDate']);
        }
        if (array_key_exists('donor', $criteria)) {
            $qb->andWhere('bh.donor = :donor')->setParameter(':donor', $criteria['donor']);
        }

        if (array_key_exists('sorting', $criteria)) {
            switch ($criteria['sorting']) {
                case 'date-asc':
                    $qb->orderBy('bh.date', 'ASC');
                    break;
                case 'date-desc':
                    $qb->orderBy('bh.date', 'DESC');
                    break;
                case 'category-asc':
                    $qb->orderBy('bh.category', 'ASC');
                    break;
                case 'category-desc':
                    $qb->orderBy('bh.category', 'DESC');
                    break;
                case 'subcategory-asc':
                    $qb->orderBy('bh.sub_category', 'ASC');
                    break;
                case 'subcategory-desc':
                    $qb->orderBy('bh.sub_category', 'DESC');
                    break;
                case 'value-asc':
                    $qb->orderBy('bh.value', 'ASC');
                    break;
                case 'value-desc':
                    $qb->orderBy('bh.value', 'DESC');
                    break;
                default:
                    $qb->orderBy('bh.date', 'DESC');
            }
        } else {
            $qb->orderBy('bh.date', 'DESC');
        }
    }

    public function acceptAllDraft(): void
    {
        $q = $this->getEntityManager()->createQuery('UPDATE ' . BankHistory::class . ' bh set bh.is_draft = 0');
        $q->execute();
    }

    public function moveTransactions(Donor $fromDonor, Donor $toDonor): void
    {
        $this->createQueryBuilder('bh')
            ->update()
            ->set('bh.donor', ':new_donor')
            ->where('bh.donor = :old_donor')
            ->setParameter('new_donor', $toDonor)
            ->setParameter('old_donor', $fromDonor)
            ->getQuery()
            ->execute();
    }


    //    /**
//     * @return BankHistory[] Returns an array of BankHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?BankHistory
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}