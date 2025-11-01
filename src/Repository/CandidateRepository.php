<?php

declare(strict_types=1);

namespace Tvdt\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Safe\DateTimeImmutable;
use Safe\Exceptions\DatetimeException;
use Safe\Exceptions\UrlException;
use Tvdt\Dto\Result;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Helpers\Base64;

/**
 * @extends ServiceEntityRepository<Candidate>
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function getCandidateByHash(Season $season, string $hash): ?Candidate
    {
        try {
            $name = Base64::base64UrlDecode($hash);
        } catch (UrlException) {
            return null;
        }

        return $this->getEntityManager()->createQuery(<<<DQL
            select c from Tvdt\Entity\Candidate c
                where c.season = :season
                and lower(c.name) = lower(:name)
            DQL
        )->setParameter('season', $season)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
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
                qc.corrections,
                max(ga.created) as end_time,
                qc.created as start_time,
                (sum(case when a.isRightAnswer = true then 1 else 0 end) + qc.corrections) as score
            from Tvdt\Entity\Candidate c
            join c.givenAnswers ga
            join ga.answer a
            join c.quizData qc
            where qc.quiz = :quiz and ga.quiz = :quiz
            group by ga.quiz, c.id, qc.id
            order by score desc, max(ga.created) - qc.created asc
            DQL
        )->setParameter('quiz', $quiz)->getResult();

        return array_map(static fn (array $row): Result => new Result(
            id: $row['id'],
            name: $row['name'],
            correct: (int) $row['correct'],
            corrections: $row['corrections'],
            time: new DateTimeImmutable($row['end_time'])->diff($row['start_time']),
            score: $row['score'],
        ), $result);
    }
}
