<?php

declare(strict_types=1);

namespace Tvdt\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Tvdt\Entity\Answer;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\BankQuestionUsage;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Exception\BankQuestionAlreadyUsedException;
use Tvdt\Exception\QuizLockedException;

final readonly class QuestionBankService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ObjectMapperInterface $objectMapper,
    ) {}

    /**
     * Copy a bank question (with its answers) into a quiz and record the usage.
     *
     * @throws QuizLockedException              when the quiz is finalized or already filled in
     * @throws BankQuestionAlreadyUsedException when the question is single-use and used, or already in this quiz
     */
    public function assignToQuiz(BankQuestion $bankQuestion, Quiz $quiz): void
    {
        if ($bankQuestion->season !== $quiz->season) {
            throw new \InvalidArgumentException('Bank question and quiz belong to different seasons');
        }

        if ($quiz->isLocked()) {
            throw new QuizLockedException();
        }

        $this->entityManager->wrapInTransaction(function () use ($bankQuestion, $quiz): void {
            // Pessimistic write lock serialises concurrent assignment attempts for the same BankQuestion
            $this->entityManager->lock($bankQuestion, LockMode::PESSIMISTIC_WRITE);

            if (!$bankQuestion->canBeAssigned() || $bankQuestion->isUsedInQuiz($quiz)) {
                throw new BankQuestionAlreadyUsedException();
            }

            $maxOrdering = 0;
            foreach ($quiz->questions as $existingQuestion) {
                $maxOrdering = max($maxOrdering, $existingQuestion->ordering);
            }

            /** @var Question $question */
            $question = $this->objectMapper->map($bankQuestion, Question::class);
            $question->ordering = $maxOrdering + 1;

            foreach ($bankQuestion->answers as $bankAnswer) {
                /** @var Answer $answer */
                $answer = $this->objectMapper->map($bankAnswer, Answer::class);
                $question->addAnswer($answer);
            }

            $quiz->addQuestion($question);

            $usage = new BankQuestionUsage($bankQuestion, $quiz);
            $usage->question = $question;

            $bankQuestion->addUsage($usage);

            $this->entityManager->persist($question);
            $this->entityManager->flush();
        });
    }

    /**
     * Propagate bank question edits to a quiz copy.
     * Only safe on quizzes where no candidate has started (no GivenAnswers exist yet).
     */
    public function syncToQuiz(BankQuestion $bankQuestion, BankQuestionUsage $usage): void
    {
        $question = $usage->question;
        if (!$question instanceof Question) {
            return;
        }

        $question->question = $bankQuestion->question;

        // Replace answers (safe: no started candidates means no GivenAnswers)
        foreach ($question->answers->toArray() as $existingAnswer) {
            $question->answers->removeElement($existingAnswer);
            $this->entityManager->remove($existingAnswer);
        }

        foreach ($bankQuestion->answers as $bankAnswer) {
            /** @var Answer $answer */
            $answer = $this->objectMapper->map($bankAnswer, Answer::class);
            $question->addAnswer($answer);
        }

        $this->entityManager->flush();
    }

    /** Remove the quiz copy created by this usage and delete the usage record. */
    public function unassignFromQuiz(BankQuestionUsage $usage): void
    {
        $question = $usage->question;
        if ($question instanceof Question) {
            $question->quiz->questions->removeElement($question);
            $this->entityManager->remove($question);
        }

        $usage->bankQuestion->usages->removeElement($usage);
        $this->entityManager->remove($usage);
        $this->entityManager->flush();
    }
}
