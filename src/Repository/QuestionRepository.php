<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Question;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findNextQuestionForCandidate(Candidate $candidate): ?Question
    {
        return $this->getEntityManager()->createQuery(<<<DQL
            select q from Tvdt\Entity\Question q
            join q.quiz qz
            where q.id not in (
                select q1.id from Tvdt\Entity\GivenAnswer ga
                join ga.answer a
                join a.question q1
                where ga.candidate = :candidate
                and q1.quiz = :quiz
            )
            and qz = :quiz
        DQL)
            ->setMaxResults(1)
            ->setParameter('candidate', $candidate)
            ->setParameter('quiz', $candidate->getSeason()->getActiveQuiz())
            ->getOneOrNullResult();
    }
}
