<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;

/**
 * @extends ServiceEntityRepository<QuizCandidate>
 */
class QuizCandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizCandidate::class);
    }

    /** @return bool true if a new entry was created */
    public function createIfNotExist(Quiz $quiz, Candidate $candidate): bool
    {
        if (0 !== $this->count(['candidate' => $candidate, 'quiz' => $quiz])) {
            return false;
        }

        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $this->getEntityManager()->persist($quizCandidate);
        $this->getEntityManager()->flush();

        return true;
    }

    public function setCorrectionsForCandidate(Quiz $quiz, Candidate $candidate, float $corrections): void
    {
        $quizCandidate = $this->findOneBy(['candidate' => $candidate, 'quiz' => $quiz]);
        if (!$quizCandidate instanceof QuizCandidate) {
            throw new \InvalidArgumentException('Quiz candidate not found');
        }

        $quizCandidate->corrections = $corrections;
        $this->getEntityManager()->flush();
    }
}
