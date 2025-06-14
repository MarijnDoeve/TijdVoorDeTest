<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Helpers\Base64;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Safe\Exceptions\UrlException;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Candidate>
 *
 * @phpstan-type Result array{id: Uuid, name: string, correct: int, time: \DateInterval, corrections: float, score: float}
 * @phpstan-type ResultList list<Result>
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
            select c from App\Entity\Candidate c
                where c.season = :season
                and lower(c.name) = lower(:name)
        DQL
        )->setParameter('season', $season)
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    public function save(Candidate $candidate, bool $flush = true): void
    {
        $this->getEntityManager()->persist($candidate);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return ResultList */
    public function getScores(Quiz $quiz): array
    {
        return $this->getEntityManager()->createQuery(<<<DQL
        select
            c.id,
            c.name,
            sum(case when a.isRightAnswer = true then 1 else 0 end) as correct,
            qc.corrections,
            max(ga.created) - qc.created                           as  time,
            (sum(case when a.isRightAnswer = true then 1 else 0 end) + qc.corrections) as score
        from App\Entity\Candidate c
        join c.givenAnswers ga
        join ga.answer a
        join c.quizData qc
        where qc.quiz = :quiz and ga.quiz = :quiz
        group by ga.quiz, c.id, qc.id
        order by score desc, time asc
        DQL
        )->setParameter('quiz', $quiz)->getResult();
    }
}
