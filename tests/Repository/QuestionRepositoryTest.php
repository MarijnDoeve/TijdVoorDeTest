<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
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

class QuestionRepositoryTest extends KernelTestCase
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
        \assert($krtekSeason instanceof Season);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Tom']);
        \assert($candidate instanceof Candidate);

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
        \assert($quiz instanceof Quiz);
        $krtekSeason->activeQuiz = $quiz;
        $this->entityManager->flush();

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame('Is de Krtek een man of een vrouw?', $question->question, 'Wrong question after switching season.');
    }

    public function testFindNextQuestionGivesNullWhenAllQuestionsAnswred(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        \assert($krtekSeason instanceof Season);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Tom']);
        \assert($candidate instanceof Candidate);

        for ($i = 0; $i < 15; ++$i) {
            $question = $this->questionRepository->findNextQuestionForCandidate($candidate);
            \assert($question instanceof Question);
            $this->answerQuestion($question, $candidate);
        }
        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);

        $this->assertNull($question);
    }

    public function testFindNextQuestionWithNoActiveQuizReturnsNull(): void
    {
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        \assert($krtekSeason instanceof Season);
        $candidate = $this->candidateRepository->findOneBy(['season' => $krtekSeason, 'name' => 'Tom']);
        \assert($candidate instanceof Candidate);

        $krtekSeason->activeQuiz = null;
        $this->entityManager->flush();

        $question = $this->questionRepository->findNextQuestionForCandidate($candidate);

        $this->assertNull($question);
    }

    private function answerQuestion(Question $question, Candidate $candidate): void
    {
        $answer = $question->answers->first();
        \assert($answer instanceof Answer);
        $this->entityManager->persist(new GivenAnswer(
            $candidate,
            $question->quiz,
            $answer,
        ));
        $this->entityManager->flush();
    }
}
