<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Repository\GivenAnswerRepository;
use Tvdt\Repository\QuizRepository;
use Tvdt\Repository\SeasonRepository;

#[CoversClass(QuizRepository::class)]
final class QuizRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private SeasonRepository $seasonRepository;

    private QuizRepository $quizRepository;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->seasonRepository = self::getContainer()->get(SeasonRepository::class);
        $this->quizRepository = self::getContainer()->get(QuizRepository::class);
        parent::setUp();
    }

    public function testClearQuiz(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBy(['seasonCode' => 'krtek']);
        $this->assertInstanceOf(Season::class, $krtekSeason);

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
        $krtekSeason = $this->seasonRepository->findOneBy(['seasonCode' => 'krtek']);
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $quiz = $krtekSeason->quizzes->last();
        $this->assertInstanceOf(Quiz::class, $quiz);

        $this->quizRepository->deleteQuiz($quiz);

        $this->entityManager->refresh($krtekSeason);

        $this->assertCount(1, $krtekSeason->quizzes);
    }
}
