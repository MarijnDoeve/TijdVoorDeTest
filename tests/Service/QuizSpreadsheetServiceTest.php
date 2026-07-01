<?php

declare(strict_types=1);

namespace Tvdt\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Exception\SpreadsheetDataException;
use Tvdt\Service\QuizSpreadsheetService;

use function Safe\file_put_contents;
use function Safe\ob_get_clean;
use function Safe\ob_start;
use function Safe\unlink;

#[CoversClass(QuizSpreadsheetService::class)]
final class QuizSpreadsheetServiceTest extends TestCase
{
    private QuizSpreadsheetService $subject;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $this->subject = new QuizSpreadsheetService();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testGenerateTemplateProducesValidXlsx(): void
    {
        $path = $this->captureXlsx($this->subject->generateTemplate());

        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            new File($path)->getMimeType(),
        );
    }

    public function testGenerateTemplateWithoutExampleHasNoQuestions(): void
    {
        $path = $this->captureXlsx($this->subject->generateTemplate(fillExample: false));

        $quiz = new Quiz();
        $this->subject->xlsxToQuiz($quiz, new File($path));

        $this->assertCount(0, $quiz->questions);
    }

    public function testGenerateTemplateExampleCanBeReimported(): void
    {
        $path = $this->captureXlsx($this->subject->generateTemplate(fillExample: true));

        $quiz = new Quiz();
        $this->subject->xlsxToQuiz($quiz, new File($path));

        $this->assertCount(1, $quiz->questions);

        /** @var Question $question */
        $question = $quiz->questions->first();
        $this->assertSame('Is de mol een man of een vrouw?', $question->question);
        $this->assertCount(2, $question->answers);
    }

    public function testQuizToXlsxEmptyQuizImportsWithNoQuestions(): void
    {
        $path = $this->captureXlsx($this->subject->quizToXlsx(new Quiz()));

        $imported = new Quiz();
        $this->subject->xlsxToQuiz($imported, new File($path));

        $this->assertCount(0, $imported->questions);
    }

    public function testQuizToXlsxRoundTrip(): void
    {
        $original = $this->makeQuiz();
        $path = $this->captureXlsx($this->subject->quizToXlsx($original));

        $imported = new Quiz();
        $this->subject->xlsxToQuiz($imported, new File($path));

        $this->assertCount(2, $imported->questions);

        /** @var Question $first */
        $first = $imported->questions->first();
        $this->assertSame('Who is de Mol?', $first->question);
        $this->assertCount(2, $first->answers);

        $answers = $first->answers->toArray();
        $this->assertSame('Alice', $answers[0]->text);
        $this->assertFalse($answers[0]->isRightAnswer);
        $this->assertSame('Bob', $answers[1]->text);
        $this->assertTrue($answers[1]->isRightAnswer);

        /** @var Question $second */
        $second = $imported->questions->last();
        $this->assertSame('What did de Mol sabotage?', $second->question);
        $this->assertCount(3, $second->answers);
    }

    public function testXlsxToQuizThrowsOnInvalidMimeType(): void
    {
        $path = $this->createTempPath('.txt');
        file_put_contents($path, 'not a spreadsheet');

        $this->expectException(\InvalidArgumentException::class);
        $this->subject->xlsxToQuiz(new Quiz(), new File($path));
    }

    public function testXlsxToQuizThrowsOnQuestionWithNoAnswers(): void
    {
        $quiz = new Quiz();
        $question = new Question();
        $question->question = 'Unanswered question';
        $question->ordering = 1;

        $quiz->addQuestion($question);

        $path = $this->captureXlsx($this->subject->quizToXlsx($quiz));

        try {
            $this->subject->xlsxToQuiz(new Quiz(), new File($path));
            $this->fail('Expected SpreadsheetDataException to be thrown');
        } catch (SpreadsheetDataException $spreadsheetDataException) {
            $this->assertNotEmpty($spreadsheetDataException->errors);
        }
    }

    private function makeQuiz(): Quiz
    {
        $quiz = new Quiz();

        $q1 = new Question();
        $q1->question = 'Who is de Mol?';
        $q1->ordering = 1;
        $q1->addAnswer(new Answer('Alice', isRightAnswer: false));
        $q1->addAnswer(new Answer('Bob', isRightAnswer: true));

        $q2 = new Question();
        $q2->question = 'What did de Mol sabotage?';
        $q2->ordering = 2;
        $q2->addAnswer(new Answer('The boat', isRightAnswer: true));
        $q2->addAnswer(new Answer('The car', isRightAnswer: false));
        $q2->addAnswer(new Answer('Nothing', isRightAnswer: false));

        $quiz->addQuestion($q1);
        $quiz->addQuestion($q2);

        return $quiz;
    }

    private function captureXlsx(\Closure $closure): string
    {
        $path = $this->createTempPath('.xlsx');
        ob_start();
        $closure();
        file_put_contents($path, ob_get_clean());

        return $path;
    }

    private function createTempPath(string $suffix): string
    {
        $path = sys_get_temp_dir().\DIRECTORY_SEPARATOR.uniqid('tvdt_test_', more_entropy: true).$suffix;
        $this->tempFiles[] = $path;

        return $path;
    }
}
