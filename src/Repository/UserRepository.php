<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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

        $this->save($user, true);
    }

    public function findByPattern(string $pattern, int $limit = 10): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('email', 'email');
        $rsm->addScalarResult('first_name', 'first_name');
        $rsm->addScalarResult('last_name', 'last_name');


        $query = $this->getEntityManager()
            ->createNativeQuery("
            SELECT
            DISTINCT id, email, first_name, last_name
          FROM
            (
              SELECT
                u.id,
                u.email,
                u.first_name,
                u.last_name
              FROM
                user u
              WHERE
                LOWER(u.email) LIKE :like_pattern
                OR LOWER(u.email) IN (:pattern)
                OR LOWER(u.first_name) LIKE :like_pattern
                OR LOWER(u.first_name) IN (:pattern)
                OR LOWER(u.last_name) LIKE :like_pattern
                OR LOWER(u.last_name) IN (:pattern)
            ) result
          LIMIT
            :limit            
            ", $rsm)
            ->setParameter('limit', $limit)
            ->setParameter('like_pattern', '%' . strtolower($pattern) . '%')
            ->setParameter('pattern', strtolower($pattern));
        $result = $query->getArrayResult();
        return $result;
    }


    /**
     * QueryBuilder na potrzeby stronicowania
     *
     * @param  mixed $criteria
     * @return QueryBuilder
     */
    public function getPagerQueryBuilder(array $criteria = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');
        $this->_buildFilteringCriteria($qb, $criteria);
        return $qb;
    }

    private function _buildFilteringCriteria(QueryBuilder $qb, array $criteria): QueryBuilder
    {
        if (array_key_exists('details', $criteria)) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        // 'LOWER(d.name) LIKE :details',
                        'LOWER(u.email) LIKE :details',
                        'LOWER(u.firstName) LIKE :details',
                        'LOWER(u.lastName) LIKE :details',
                    )
                )
                ->setParameter('details', '%' . strtolower(trim($criteria['details'])) . '%');
        }

        if (array_key_exists('sorting', $criteria)) {
            switch ($criteria['sorting']) {
                case 'email-asc':
                    $qb->orderBy('u.email', 'ASC');
                    break;
                case 'email-desc':
                    $qb->orderBy('u.email', 'DESC');
                    break;
                case 'first_name-asc':
                    $qb->orderBy('u.firstName', 'ASC');
                    break;
                case 'first_name-desc':
                    $qb->orderBy('u.firstName', 'DESC');
                    break;
                case 'last_name-asc':
                    $qb->orderBy('u.lastName', 'ASC');
                    break;
                case 'last_name-desc':
                    $qb->orderBy('u.lastName', 'DESC');
                    break;
                case 'created_at-asc':
                    $qb->orderBy('u.createdAt', 'ASC');
                    break;
                case 'created_at-desc':
                    $qb->orderBy('u.createdAt', 'DESC');
                    break;
                case 'login_success-asc':
                    $qb->orderBy('u.loginSuccess', 'ASC');
                    break;
                case 'login_success-desc':
                    $qb->orderBy('u.loginSuccess', 'DESC');
                    break;
                default:
                    $qb->orderBy('u.email', 'DESC');
            }
        } else {
            $qb->orderBy('u.lastName', 'ASC');
        }

        return $qb;
    }

    //    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}