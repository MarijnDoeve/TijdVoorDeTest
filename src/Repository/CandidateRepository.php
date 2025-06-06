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
 * @phpstan-type Result array{id: Uuid, name: string, correct: int, time: \DateInterval, corrections?: float, score: float}
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

    /** @return ResultList */
    public function getScores(Quiz $quiz): array
    {
        $scoreQb = $this->createQueryBuilder('c', 'c.id')
            ->select('c.id', 'c.name', 'sum(case when a.isRightAnswer = true then 1 else 0 end) as correct')
            ->join('c.givenAnswers', 'ga')
            ->join('ga.answer', 'a')
            ->where('ga.quiz = :quiz')
            ->groupBy('c.id')
            ->setParameter('quiz', $quiz);

        $startTimeCorrectionQb = $this->createQueryBuilder('c', 'c.id')
            ->select('c.id', 'qc.corrections', 'max(ga.created) - qc.created as time')
            ->join('c.quizData', 'qc')
            ->join('c.givenAnswers', 'ga')
            ->where('qc.quiz = :quiz')
            ->groupBy('ga.quiz', 'c.id', 'qc.id')
            ->setParameter('quiz', $quiz);

        $merged = array_merge_recursive(
            $scoreQb->getQuery()->getArrayResult(),
            $startTimeCorrectionQb->getQuery()->getArrayResult(),
        );

        return $this->sortResults($this->calculateScore($merged));
    }

    /**
     * @param array<string, array{id: Uuid, name: string, correct: int, time: \DateInterval, corrections?: float}> $in
     *
     * @return array<string, Result>
     * */
    private function calculateScore(array $in): array
    {
        return array_map(static fn ($candidate): array => [
            ...$candidate,
            'score' => $candidate['correct'] + ($candidate['corrections'] ?? 0.0),
        ], $in);
    }

    /**
     * @param array<string, Result> $results
     *
     * @return ResultList
     * */
    private function sortResults(array $results): array
    {
        usort($results, static fn ($a, $b): int => $b['score'] <=> $a['score']);

        return $results;
    }
}
