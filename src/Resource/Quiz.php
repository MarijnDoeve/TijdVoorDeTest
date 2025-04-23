<?php

declare(strict_types=1);

namespace App\Resource;

use App\Entity\Answer;
use App\Entity\Quiz as QuizEntity;
use Doctrine\Common\Collections\Collection;

final class Quiz
{
    /** @param array<string, array<string, bool>> $questions*/
    public function __construct(
        public array $questions,
    ) {}

    public static function fromEntity(QuizEntity $quiz): self
    {
        $questions = [];

        foreach ($quiz->getQuestions() as $question) {
            $questions[$question->getQuestion()] = self::answerArray($question->getAnswers());
        }

        return new self($questions);
    }

    /** @param Collection<int, Answer> $answers
     * @return array<string, bool>
     **/
    private static function answerArray(Collection $answers): array
    {
        $result = [];

        foreach ($answers as $answer) {
            $result[$answer->getText()] = $answer->isRightAnswer();
        }

        return $result;
    }
}
