<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use Tvdt\Entity\Quiz;
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
        $this->markTestIncomplete('TODO: Make fixtures first and write good test.');
    }
}
