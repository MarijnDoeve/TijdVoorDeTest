<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Correction;
use App\Entity\Quiz;
use App\Entity\Season;
use App\Helpers\Base64;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Safe\Exceptions\UrlException;

/**
 * @extends ServiceEntityRepository<Candidate>
 *
 * @phpstan-type ResultArray array<string, array{0: Candidate, correct: int, time: DateInterval, corrections?: float, score: float}>
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

        return $this->createQueryBuilder('c')
            ->where('c.season = :season')
            ->andWhere('lower(c.name) = lower(:name)')
            ->setParameter('season', $season)
            ->setParameter('name', $name)
            ->getQuery()->getOneOrNullResult();
    }

    public function save(Candidate $candidate, bool $flush = true): void
    {
        $this->getEntityManager()->persist($candidate);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return ResultArray */
    public function getScores(Quiz $quiz): array
    {
        $scoreTimeQb = $this->createQueryBuilder('c', 'c.id')
            ->select('c', 'sum(case when a.isRightAnswer = true then 1 else 0 end) as correct', 'max(ga.created) - min(ga.created) as time')
            ->join('c.givenAnswers', 'ga')
            ->join('ga.answer', 'a')
            ->where('ga.quiz = :quiz')
            ->groupBy('c.id')
            ->setParameter('quiz', $quiz);

        $correctionsQb = $this->createQueryBuilder('c', 'c.id')
            ->select('c', 'cor.amount as corrections')
            ->innerJoin(Correction::class, 'cor', Join::WITH, 'cor.candidate = c and cor.quiz = :quiz')
            ->setParameter('quiz', $quiz);

        $merged = array_merge_recursive($scoreTimeQb->getQuery()->getArrayResult(), $correctionsQb->getQuery()->getArrayResult());

        return $this->sortResults($this->calculateScore($merged));
    }

    /**
     * @param array<string, array{0: Candidate, correct: int, time: \DateInterval, corrections?: float}> $in
     *
     * @return ResultArray
     */
    private function calculateScore(array $in): array
    {
        return array_map(static fn ($candidate): array => [
            ...$candidate,
            'score' => $candidate['correct'] + ($candidate['corrections'] ?? 0.0),
        ], $in);
    }

    /**
     * @param ResultArray $results
     *
     * @return ResultArray
     */
    private function sortResults(array $results): array
    {
        usort($results, static fn ($a, $b): int => $b['score'] <=> $a['score']);

        return $results;
    }
}
