<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Safe\DateTimeImmutable;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;

/** @extends ServiceEntityRepository<QuizCandidate> */
class QuizCandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizCandidate::class);
    }

    /** @return bool|null true if a new entry was created, false if it already exists, null if candidate is inactive */
    public function createIfNotExist(Quiz $quiz, Candidate $candidate): ?bool
    {
        $quizCandidate = $this->findOneBy(['candidate' => $candidate, 'quiz' => $quiz]);

        if (null !== $quizCandidate) {
            // Check if candidate is inactive
            if (!$quizCandidate->active) {
                return null;
            }

            // If QuizCandidate exists but hasn't started yet, set the started timestamp
            if (null === $quizCandidate->started) {
                $quizCandidate->started = new DateTimeImmutable();
                $this->getEntityManager()->flush();
            }

            return false;
        }

        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $quizCandidate->started = new DateTimeImmutable();
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

    public function setPenaltyForCandidate(Quiz $quiz, Candidate $candidate, int $penalty): void
    {
        $quizCandidate = $this->findOneBy(['candidate' => $candidate, 'quiz' => $quiz]);
        if (!$quizCandidate instanceof QuizCandidate) {
            throw new \InvalidArgumentException('Quiz candidate not found');
        }

        $quizCandidate->penaltySeconds = $penalty;
        $this->getEntityManager()->flush();
    }
}
