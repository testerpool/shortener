<?php

namespace App\Repository;

use App\Entity\UrlMinimizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UrlMinimizer>
 *
 * @method UrlMinimizer|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlMinimizer|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlMinimizer[]    findAll()
 * @method UrlMinimizer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlMinimizerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlMinimizer::class);
    }

    public function save(UrlMinimizer $urlMinimizer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($urlMinimizer);
        $entityManager->flush();
    }

    /**
     * @return UrlMinimizer[] Returns an array of UrlMinimizer objects
     */
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySomeField($value): ?UrlMinimizer
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
