<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\QuestionLabel;
use Tvdt\Entity\Season;

/** @extends ServiceEntityRepository<BankQuestion> */
class BankQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankQuestion::class);
    }

    /** @return list<BankQuestion> */
    public function findBySeason(Season $season, ?QuestionLabel $label = null): array
    {
        $queryBuilder = $this->createQueryBuilder('bq')
            ->select('bq', 'ba', 'l', 'u', 'uq')
            ->leftJoin('bq.answers', 'ba')
            ->leftJoin('bq.labels', 'l')
            ->leftJoin('bq.usages', 'u')
            ->leftJoin('u.quiz', 'uq')
            ->where('bq.season = :season')
            ->orderBy('bq.question', 'ASC')
            ->setParameter('season', $season);

        if ($label instanceof QuestionLabel) {
            $queryBuilder
                ->andWhere(':label member of bq.labels')
                ->setParameter('label', $label);
        }

        /* @var list<BankQuestion> */
        return $queryBuilder->getQuery()->getResult();
    }
}
