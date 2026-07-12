<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Quiz;

/** @extends ServiceEntityRepository<GivenAnswer> */
class GivenAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GivenAnswer::class);
    }

    public function deleteAllForCandidateInQuiz(Quiz $quiz, Candidate $candidate): void
    {
        $givenAnswers = $this->findBy(['quiz' => $quiz, 'candidate' => $candidate]);

        foreach ($givenAnswers as $givenAnswer) {
            $this->getEntityManager()->remove($givenAnswer);
        }

        $this->getEntityManager()->flush();
    }
}
