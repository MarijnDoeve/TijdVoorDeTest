<?php

declare(strict_types=1);

namespace Tvdt\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tvdt\Entity\Answer;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\BankQuestionUsage;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Exception\BankQuestionAlreadyUsedException;
use Tvdt\Exception\QuizLockedException;

final readonly class QuestionBankService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

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

        if (!$bankQuestion->canBeAssigned() || $bankQuestion->isUsedInQuiz($quiz)) {
            throw new BankQuestionAlreadyUsedException();
        }

        $maxOrdering = 0;
        foreach ($quiz->questions as $existingQuestion) {
            $maxOrdering = max($maxOrdering, $existingQuestion->ordering);
        }

        $question = new Question();
        $question->question = $bankQuestion->question;
        $question->ordering = $maxOrdering + 1;

        foreach ($bankQuestion->answers as $bankAnswer) {
            $answer = new Answer($bankAnswer->text, $bankAnswer->isRightAnswer);
            $answer->ordering = $bankAnswer->ordering;
            $question->addAnswer($answer);
        }

        $quiz->addQuestion($question);
        $bankQuestion->addUsage(new BankQuestionUsage($bankQuestion, $quiz));

        $this->entityManager->persist($question);
        $this->entityManager->flush();
    }
}
