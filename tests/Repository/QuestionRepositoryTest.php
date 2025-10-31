<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Repository\CandidateRepository;
use Tvdt\Repository\QuestionRepository;
use Tvdt\Repository\SeasonRepository;

#[CoversClass(QuestionRepository::class)]
final class QuestionRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private QuestionRepository $questionRepository;

    private SeasonRepository $seasonRepository;

    private CandidateRepository $candidateRepository;

    protected function setUp(): void
    {
        $container = self::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->questionRepository = $container->get(QuestionRepository::class);
        $this->seasonRepository = $container->get(SeasonRepository::class);
        $this->candidateRepository = $container->get(CandidateRepository::class);
    }

    public function testFindNextQuestionReturnsRightQuestion(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Tom']);
        $this->assertInstanceOf(Candidate::class, $candidate);

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame('Is de Krtek een man of een vrouw?', $question->question, 'Wrong first question');

        $this->answerQuestion($question, $candidate);

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame('Hoeveel broers heeft de Krtek?', $question->question, 'Wrong second question');

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame('Hoeveel broers heeft de Krtek?', $question->question, 'Getting question a second time fails');

        $quiz = $krtekSeason->quizzes->last();
        $this->assertInstanceOf(Quiz::class, $quiz);
        $krtekSeason->activeQuiz = $quiz;
        $this->entityManager->flush();

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame('Is de Krtek een man of een vrouw?', $question->question, 'Wrong question after switching season.');
    }

    public function testFindNextQuestionGivesNullWhenAllQuestionsAnswered(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Tom']);
        $this->assertInstanceOf(Candidate::class, $candidate);

        for ($i = 0; $i < 15; ++$i) {
            $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
            $this->assertInstanceOf(Question::class, $question);
            $this->answerQuestion($question, $candidate);
        }

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);

        $this->assertNotInstanceOf(Question::class, $question);
    }

    public function testFindNextQuestionWithNoActiveQuizReturnsNull(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $krtekSeason);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Tom']);
        $this->assertInstanceOf(Candidate::class, $candidate);

        $krtekSeason->activeQuiz = null;
        $this->entityManager->flush();

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);

        $this->assertNotInstanceOf(Question::class, $question);
    }

    private function answerQuestion(Question $question, Candidate $candidate): void
    {
        $answer = $question->answers->first();
        $this->assertInstanceOf(Answer::class, $answer);
        $this->entityManager->persist(new GivenAnswer(
            $candidate,
            $question->quiz,
            $answer,
        ));
        $this->entityManager->flush();
    }
}
