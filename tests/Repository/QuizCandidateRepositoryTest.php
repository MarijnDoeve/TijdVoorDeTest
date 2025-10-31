<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Entity\Season;
use Tvdt\Repository\CandidateRepository;
use Tvdt\Repository\QuizCandidateRepository;
use Tvdt\Repository\SeasonRepository;

#[CoversClass(QuizCandidateRepository::class)]
final class QuizCandidateRepositoryTest extends KernelTestCase
{
    private SeasonRepository $seasonRepository;

    private QuizCandidateRepository $quizCandidateRepository;

    private CandidateRepository $candidateRepository;

    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->seasonRepository = $container->get(SeasonRepository::class);
        $this->quizCandidateRepository = $container->get(QuizCandidateRepository::class);
        $this->candidateRepository = $container->get(CandidateRepository::class);
    }

    public function testCreateIfNotExists(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Myrthe']);
        $this->assertInstanceOf(Candidate::class, $candidate);
        $quiz = $krtekSeason->activeQuiz;
        $this->assertInstanceOf(Quiz::class, $quiz);

        $result = $this->quizCandidateRepository->createIfNotExist($quiz, $candidate);
        $this->assertTrue($result);

        $quizCandidate = $this->quizCandidateRepository->findOneBy([
            'candidate' => $candidate,
            'quiz' => $quiz,
        ]);

        $this->assertInstanceOf(QuizCandidate::class, $quizCandidate);

        $result = $this->quizCandidateRepository->createIfNotExist($quiz, $candidate);
        $this->assertFalse($result);
    }

    public function testSetCorrectionsForCandidateUpdatesCandidateCorrectly(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Myrthe']);
        $this->assertInstanceOf(Candidate::class, $candidate);
        $quiz = $krtekSeason->activeQuiz;
        $this->assertInstanceOf(Quiz::class, $quiz);

        $this->quizCandidateRepository->createIfNotExist($quiz, $candidate);

        $this->quizCandidateRepository->setCorrectionsForCandidate(
            $quiz, $candidate, 3.5,
        );

        $quizCandidate = $this->quizCandidateRepository->findOneBy([
            'candidate' => $candidate,
            'quiz' => $quiz,
        ]);

        $this->assertInstanceOf(QuizCandidate::class, $quizCandidate);

        $this->assertEqualsWithDelta(3.5, $quizCandidate->corrections, 0.1);
    }

    public function testCannotGiveCorrectionsToCandidateWithoutResult(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Myrthe']);
        $this->assertInstanceOf(Candidate::class, $candidate);
        $quiz = $krtekSeason->activeQuiz;
        $this->assertInstanceOf(Quiz::class, $quiz);

        $this->expectException(\InvalidArgumentException::class);

        $this->quizCandidateRepository->setCorrectionsForCandidate(
            $quiz, $candidate, 3.5,
        );
    }
}
