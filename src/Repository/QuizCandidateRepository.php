<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Quiz;
use App\Entity\QuizCandidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
