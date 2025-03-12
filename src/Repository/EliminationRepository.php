<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Elimination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Elimination>
 */
class EliminationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Elimination::class);
    }

    //    /**
    //     * @return Elimination[] Returns an array of Elimination objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Elimination
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
