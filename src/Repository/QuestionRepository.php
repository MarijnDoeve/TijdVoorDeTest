<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            select q from App\Entity\Question q
            join q.quiz qz
            where q.id not in (
                select q1.id from App\Entity\GivenAnswer ga
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
