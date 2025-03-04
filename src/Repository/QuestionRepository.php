<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\GivenAnswer;
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

    public function findNextQuestionForCandidate(Candidate $candidate): Question
    {
        $qb = $this->createQueryBuilder('q');

        return $qb->join('q.quiz', 'qz')
            ->andWhere($qb->expr()->notIn('q.id', $this->getEntityManager()->createQueryBuilder()
                ->select('ga.id')
                ->from(GivenAnswer::class, 'ga')
                ->join('ga.answer', 'a')
                ->join('a.question', 'q1')
                ->andWhere($qb->expr()->isNotNull('ga.answer'))
                ->andWhere('ga.candidate = :candidate')
                ->andWhere('q1.quiz = :quiz')
                ->getDQL()))
            ->andWhere('qz = :quiz')
            ->setMaxResults(1)
            ->setParameter('candidate', $candidate)
            ->setParameter('quiz', $candidate->getSeason()->getActiveQuiz())
        ->getQuery()->getSingleResult();
    }
}
