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
            ->where('bq.season = :season')
            ->orderBy('bq.question', 'ASC')
            ->setParameter('season', $season);

        if ($label instanceof QuestionLabel) {
            $queryBuilder
                ->andWhere(':label member of bq.labels')
                ->setParameter('label', $label);
        }

        /** @var list<BankQuestion> $questions */
        $questions = $queryBuilder->getQuery()->getResult();

        if ([] === $questions) {
            return [];
        }

        // Load each many-to-many/one-to-many collection in a separate query to avoid
        // the Cartesian-product row explosion that occurs when joining multiple collections at once.
        $this->createQueryBuilder('bq')
            ->select('partial bq.{id}', 'ba')
            ->leftJoin('bq.answers', 'ba')
            ->where('bq.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('bq')
            ->select('partial bq.{id}', 'l')
            ->leftJoin('bq.labels', 'l')
            ->where('bq.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult();

        $this->createQueryBuilder('bq')
            ->select('partial bq.{id}', 'u', 'uq')
            ->leftJoin('bq.usages', 'u')
            ->leftJoin('u.quiz', 'uq')
            ->where('bq.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult();

        return $questions;
    }
}
