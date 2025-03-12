<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GivenAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GivenAnswer>
 */
class GivenAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GivenAnswer::class);
    }

    public function save(GivenAnswer $givenAnswer, bool $flush = true): void
    {
        $this->getEntityManager()->persist($givenAnswer);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
