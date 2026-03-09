<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Safe\DateTimeImmutable;
use Safe\Exceptions\DatetimeException;
use Symfony\Component\Uid\Uuid;
use Tvdt\Dto\Result;
use Tvdt\Entity\Quiz;
use Tvdt\Exception\ErrorClearingQuizException;

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
            $em->createQuery(<<<DQL
                delete from Tvdt\Entity\QuizCandidate qc
                where qc.quiz = :quiz
                DQL)
                ->setParameter('quiz', $quiz)
                ->execute();

            $em->createQuery(<<<DQL
                delete from Tvdt\Entity\GivenAnswer ga
                where ga.quiz = :quiz
                DQL)
                ->setParameter('quiz', $quiz)
                ->execute();

            $em->createQuery(<<<DQL
                delete from Tvdt\Entity\Elimination e
                where e.quiz = :quiz
                DQL)
                ->setParameter('quiz', $quiz)
                ->execute();
        }
        // @codeCoverageIgnoreStart
        catch (\Throwable $throwable) {
            $this->logger->error($throwable->getMessage());
            $em->rollback();
            throw new ErrorClearingQuizException(previous: $throwable);
        }

        // @codeCoverageIgnoreEnd

        $em->commit();
    }

    public function deleteQuiz(Quiz $quiz): void
    {
        $this->getEntityManager()->remove($quiz);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws DatetimeException
     *
     * @return list<Result>
     */
    public function getScores(Quiz $quiz): array
    {
        $result = $this->getEntityManager()->createQuery(<<<DQL
            select
                c.id,
                c.name,
                sum(case when a.isRightAnswer = true then 1 else 0 end) as correct,
                qd.corrections,
                qd.penaltySeconds,
                max(ga.created) as end_time,
                qd.created as start_time,
                (sum(case when a.isRightAnswer = true then 1 else 0 end) + qd.corrections) as score
            from Tvdt\Entity\Candidate c
            join c.givenAnswers ga
            join ga.answer a
            join c.quizData qd
            where qd.quiz = :quiz and ga.quiz = :quiz
            group by ga.quiz, c.id, qd.id
            order by score desc, max(ga.created) - qd.created asc
            DQL
        )->setParameter('quiz', $quiz)->getResult();

        return array_map(static fn (array $row): Result => new Result(
            id: $row['id'],
            name: $row['name'],
            correct: (int) $row['correct'],
            corrections: $row['corrections'],
            penaltySeconds: $row['penaltySeconds'],
            time: $row['start_time']->diff(new DateTimeImmutable($row['end_time'])),
            score: $row['score'],
        ), $result);
    }

    public function fetchWithQuestions(Uuid $id): Quiz
    {
        return $this->getEntityManager()->createQuery(<<<dql
            select q, qz, a from Tvdt\Entity\Quiz q
            join q.questions qz
            join qz.answers a
            where q.id = :id
            dql)->setParameter('id', $id)->getSingleResult();
    }

    /**
     * Fetch quiz with all relations needed for error checking.
     * This includes: questions, answers, answer candidates, and season candidates.
     */
    public function fetchWithQuestionsAndCandidates(Uuid $id): Quiz
    {
        return $this->getEntityManager()->createQuery(<<<dql
            select q, qz, a, ac, s, sc from Tvdt\Entity\Quiz q
            join q.questions qz
            join qz.answers a
            left join a.candidates ac
            join q.season s
            left join s.candidates sc
            where q.id = :id
            dql)->setParameter('id', $id)->getSingleResult();
    }
}
