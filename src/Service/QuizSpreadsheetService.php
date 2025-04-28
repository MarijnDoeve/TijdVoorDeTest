<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Exception\SpreadsheetDataException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\File\File;

class QuizSpreadsheetService
{
    public function generateTemplate(bool $fillExample = true): \Closure
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $sheet->setCellValue('A1', 'Question');
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getStyle('A:A')->getAlignment()->setWrapText(true);

        $counter = 1;
        foreach (range('B', 'L', 2) as $column) {
            $sheet->setCellValue($column.'1', 'Answer '.$counter++);
            $sheet->getColumnDimension($column)->setWidth(30);
            $sheet->getStyle($column.':'.$column)->getAlignment()->setWrapText(true);
        }

        foreach (range('C', 'M', 2) as $column) {
            $sheet->setCellValue($column.'1', 'Correct');
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        if ($fillExample) {
            $sheet->setCellValue('B2', 'Man');
            $sheet->setCellValue('C2', true);

            $sheet->setCellValue('D2', 'Vrouw');
            $sheet->setCellValue('E2', false);

            $sheet->setCellValue('A2', 'Is de mol een man of een vrouw?');
        }

        return $this->toXlsx($spreadsheet);
    }

    /** @throws SpreadsheetDataException */
    public function xlsxToQuiz(Quiz $quiz, File $file): Quiz
    {
        $spreadsheet = $this->readSheet($file);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());

        $answerLines = \array_slice($sheet->toArray(formatData: false), 1);

        return $this->fillQuizFromArray($quiz, $answerLines);
    }

    private function readSheet(File $file): Spreadsheet
    {
        return (new \PhpOffice\PhpSpreadsheet\Reader\Xlsx())->setReadDataOnly(true)->load($file->getRealPath());
    }

    /**
     * @param array<int, array<int, string|bool|null>> $sheet
     *
     * @throws SpreadsheetDataException
     */
    private function fillQuizFromArray(Quiz $quiz, array $sheet): Quiz
    {
        $errors = [];

        $questionCounter = 1;
        foreach ($sheet as $questionArr) {
            if (null === $questionArr[0]) {
                break;
            }

            $question = new Question();
            $question->setQuestion((string) $questionArr[0]);
            $question->setOrdering($questionCounter++);

            $answerCounter = 1;
            $arrCounter = 1;

            while (true) {
                if (null === $questionArr[$arrCounter]) {
                    if (1 === $answerCounter) {
                        $errors[] = \sprintf('Question %d has no answers', $answerCounter);
                    }
                    break;
                }

                $answer = new Answer((string) $questionArr[$arrCounter++], (bool) $questionArr[$arrCounter++]);
                $answer->setOrdering($answerCounter++);
                $question->addAnswer($answer);
            }

            $quiz->addQuestion($question);
        }

        if ([] !== $errors) {
            throw new SpreadsheetDataException($errors);
        }

        return $quiz;
    }

    public function quizToXlsx(Quiz $quiz): void {}

    private function toXlsx(Spreadsheet $spreadsheet): \Closure
    {
        $writer = new Xlsx($spreadsheet);

        return static fn () => $writer->save('php://output');
    }
}
