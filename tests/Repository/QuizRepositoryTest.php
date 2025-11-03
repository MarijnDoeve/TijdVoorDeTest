<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Repository\GivenAnswerRepository;
use Tvdt\Repository\QuizRepository;

#[CoversClass(QuizRepository::class)]
final class QuizRepositoryTest extends DatabaseTestCase
{
    public function testClearQuiz(): void
    {
        $krtekSeason = $this->getSeasonByCode('krtek');
        $quiz = $krtekSeason->activeQuiz;
        $this->assertInstanceOf(Quiz::class, $quiz);

        $this->quizRepository->clearQuiz($quiz);

        $this->entityManager->refresh($krtekSeason);

        $this->assertEmpty($quiz->candidateData);
        $this->assertEmpty($quiz->eliminations);

        /** @var GivenAnswerRepository $givenAnswerRepository */
        $givenAnswerRepository = self::getContainer()->get(GivenAnswerRepository::class);
        $this->assertEmpty($givenAnswerRepository->findBy(['quiz' => $quiz]));
    }

    public function testDeleteQuiz(): void
    {
        $krtekSeason = $this->getSeasonByCode('krtek');
        $quiz = $krtekSeason->quizzes->last();
        $this->assertInstanceOf(Quiz::class, $quiz);

        $this->quizRepository->deleteQuiz($quiz);

        $this->entityManager->refresh($krtekSeason);

        $this->assertCount(1, $krtekSeason->quizzes);
    }

    public function testGetScores(): void
    {
        $clock = new MockClock('2025-11-01 16:00:00');
        self::getContainer()->set(ClockInterface::class, $clock);
        $krtekSeason = $this->getSeasonByCode('krtek');
        $candidate = $this->getCandidateBySeasonAndName($krtekSeason, 'Iris');

        // Start Quiz
        $qc = new QuizCandidate($krtekSeason->activeQuiz, $candidate);
        $this->entityManager->persist($qc);
        $this->entityManager->flush();

        dump($qc->created);

        $this->markTestIncomplete('TODO: Make fixtures first and write good test.');
    }
}
