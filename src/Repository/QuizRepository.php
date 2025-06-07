<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Elimination;
use App\Entity\GivenAnswer;
use App\Entity\Quiz;
use App\Entity\QuizCandidate;
use App\Exception\ErrorClearingQuizException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly LoggerInterface $logger)
    {
        parent::__construct($registry, Quiz::class);
    }

    /** @throws ErrorClearingQuizException */
    public function clearQuiz(Quiz $quiz): void
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();
        try {
            $em->createQueryBuilder()
                ->delete()->from(QuizCandidate::class, 'qc')
                ->where('qc.quiz = :quiz')
                ->setParameter('quiz', $quiz)
                ->getQuery()->execute();

            $em->createQueryBuilder()
                ->delete()->from(GivenAnswer::class, 'ga')
                ->where('ga.quiz = :quiz')
                ->setParameter('quiz', $quiz)
                ->getQuery()->execute();
            $em->createQueryBuilder()
                ->delete()->from(Elimination::class, 'e')
                ->where('e.quiz = :quiz')
                ->setParameter('quiz', $quiz)
                ->getQuery()->execute();
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            $em->rollback();
            throw new ErrorClearingQuizException(previous: $throwable);
        }

        $em->commit();
    }

    public function deleteQuiz(Quiz $quiz): void
    {
        $this->getEntityManager()->remove($quiz);
        $this->getEntityManager()->flush();
    }
}
