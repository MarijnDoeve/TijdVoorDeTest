<?php

declare(strict_types=1);

namespace Tvdt\Service;

use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer;
use Safe\Exceptions\FilesystemException;
use Tvdt\Dto\Result;
use Tvdt\Entity\BankQuestionUsage;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\QuestionLabel;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;
use Tvdt\Repository\QuizRepository;

use function Safe\preg_replace;
use function Safe\tempnam;
use function Safe\unlink;

/** Builds a GDPR data-portability export (a zip of xlsx files) for everything owned by a single user. */
class DataExportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuizSpreadsheetService $quizSpreadsheetService,
        private readonly QuizRepository $quizRepository,
    ) {}

    /** @throws FilesystemException @return string path to a temp zip file; caller is responsible for removing it */
    public function exportForUser(User $user): string
    {
        $filter = $this->entityManager->getFilters();
        $filter->disable('softdeleteable');

        try {
            return $this->buildZip($user);
        } finally {
            $filter->enable('softdeleteable');
        }
    }

    private function buildZip(User $user): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'tvdt_export_');
        $tempXlsxFiles = [];

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::OVERWRITE);

        try {
            $profilePath = $this->writeToTempFile($this->buildProfileWorkbook($user));
            $tempXlsxFiles[] = $profilePath;
            $zip->addFile($profilePath, 'profile.xlsx');

            foreach ($user->seasons as $season) {
                $folder = self::sanitizeForPath($season->seasonCode.'-'.$season->name).'/';

                foreach ($season->quizzes as $quiz) {
                    $quizPath = $this->writeToTempFile($this->buildQuizWorkbook($quiz));
                    $tempXlsxFiles[] = $quizPath;
                    $zip->addFile($quizPath, $folder.self::sanitizeForPath($quiz->name).'.xlsx');
                }

                $candidatesPath = $this->writeToTempFile($this->buildCandidatesWorkbook($season));
                $tempXlsxFiles[] = $candidatesPath;
                $zip->addFile($candidatesPath, $folder.'candidates.xlsx');

                $questionBankPath = $this->writeToTempFile($this->buildQuestionBankWorkbook($season));
                $tempXlsxFiles[] = $questionBankPath;
                $zip->addFile($questionBankPath, $folder.'question-bank.xlsx');
            }

            $zip->close();
        } finally {
            foreach ($tempXlsxFiles as $tempXlsxFile) {
                unlink($tempXlsxFile);
            }
        }

        return $zipPath;
    }

    private function buildProfileWorkbook(User $user): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $account = $spreadsheet->getActiveSheet();
        $account->setTitle('Account');
        $account->getStyle('A:A')->getFont()->setBold(true);
        $account->fromArray([
            ['Email', $user->email],
            ['Roles', implode(', ', $user->getRoles())],
            ['Email verified', $user->isVerified ? 'Yes' : 'No'],
            ['Account ID', $user->id->toString()],
        ], null, 'A1');
        $account->getColumnDimension('A')->setAutoSize(true);
        $account->getColumnDimension('B')->setAutoSize(true);

        $seasons = $spreadsheet->createSheet();
        $seasons->setTitle('Seasons');
        $seasons->fromArray(['Season', 'Season code', 'Quizzes', 'Candidates', 'Shared with other owners'], null, 'A1');
        $seasons->getStyle('1:1')->getFont()->setBold(true);

        $row = 2;
        foreach ($user->seasons as $season) {
            $seasons->fromArray([
                $season->name,
                $season->seasonCode,
                $season->quizzes->count(),
                $season->candidates->count(),
                $season->owners->count() > 1 ? 'Yes' : 'No',
            ], null, 'A'.$row);
            ++$row;
        }

        foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
            $seasons->getColumnDimension($column)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildQuizWorkbook(Quiz $quiz): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $questions = $spreadsheet->getActiveSheet();
        $questions->setTitle('Questions');

        $this->quizSpreadsheetService->fillQuestionsSheet($questions, $quiz);

        $results = $spreadsheet->createSheet();
        $results->setTitle('Results');
        $this->fillResultsSheet($results, $quiz);

        $eliminations = $spreadsheet->createSheet();
        $eliminations->setTitle('Eliminations');
        $this->fillEliminationsSheet($eliminations, $quiz);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillResultsSheet(Worksheet $sheet, Quiz $quiz): void
    {
        $sheet->fromArray(['Candidate', 'Correct answers', 'Corrections', 'Penalty (s)', 'Score', 'Time', 'Started', 'Active', 'Deleted'], null, 'A1');
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        /** @var array<string, Result> $scoresByCandidateId */
        $scoresByCandidateId = [];
        foreach ($this->quizRepository->getScores($quiz) as $result) {
            $scoresByCandidateId[$result->id->toString()] = $result;
        }

        $row = 2;
        foreach ($quiz->candidateData as $quizCandidate) {
            $candidate = $quizCandidate->candidate;
            $result = $scoresByCandidateId[$candidate->id->toString()] ?? null;

            $sheet->fromArray([
                $candidate->name,
                $result?->correct,
                $result?->corrections,
                $result?->penaltySeconds,
                $result?->score,
                $result instanceof Result ? $result->time->format('%i:%S') : null,
                $quizCandidate->started?->format(\DateTimeInterface::ATOM),
                $quizCandidate->active ? 'Yes' : 'No',
                $quizCandidate->getDeletedAt()?->format(\DateTimeInterface::ATOM) ?? '',
            ], null, 'A'.$row);
            ++$row;
        }

        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function fillEliminationsSheet(Worksheet $sheet, Quiz $quiz): void
    {
        /** @var list<Candidate> $candidates */
        $candidates = $quiz->season->candidates->toArray();

        $header = ['Prepared at', 'Deleted'];
        foreach ($candidates as $candidate) {
            $header[] = $candidate->name;
        }

        $sheet->fromArray($header, null, 'A1');
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $row = 2;
        foreach ($quiz->eliminations as $elimination) {
            $line = [
                $elimination->getCreatedAt()?->format(\DateTimeInterface::ATOM) ?? '',
                $elimination->getDeletedAt()?->format(\DateTimeInterface::ATOM) ?? '',
            ];

            foreach ($candidates as $candidate) {
                $line[] = $elimination->getScreenColour($candidate->name) ?? '';
            }

            $sheet->fromArray($line, null, 'A'.$row);
            ++$row;
        }

        foreach (range('A', Coordinate::stringFromColumnIndex(2 + \count($candidates))) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function buildCandidatesWorkbook(Season $season): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $candidatesSheet = $spreadsheet->getActiveSheet();
        $candidatesSheet->setTitle('Candidates');
        $candidatesSheet->fromArray(['Name', 'Public link identifier'], null, 'A1');
        $candidatesSheet->getStyle('1:1')->getFont()->setBold(true);

        $row = 2;
        foreach ($season->candidates as $candidate) {
            $candidatesSheet->fromArray([$candidate->name, $candidate->nameHash], null, 'A'.$row);
            ++$row;
        }

        $candidatesSheet->getColumnDimension('A')->setAutoSize(true);
        $candidatesSheet->getColumnDimension('B')->setAutoSize(true);

        $infoSheet = $spreadsheet->createSheet();
        $infoSheet->setTitle('Season info');
        $infoSheet->getStyle('A:A')->getFont()->setBold(true);
        $infoSheet->fromArray([
            ['Season name', $season->name],
            ['Season code', $season->seasonCode],
            ['Number of quizzes', $season->quizzes->count()],
            ['Number of candidates', $season->candidates->count()],
            ['Active quiz', $season->activeQuiz instanceof Quiz ? $season->activeQuiz->name : ''],
            ['Show numbers', $season->settings?->showNumbers ? 'Yes' : 'No'],
            ['Confirm answers', $season->settings?->confirmAnswers ? 'Yes' : 'No'],
            ['Shared with other owners', $season->owners->count() > 1 ? 'Yes' : 'No'],
        ], null, 'A1');
        $infoSheet->getColumnDimension('A')->setAutoSize(true);
        $infoSheet->getColumnDimension('B')->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildQuestionBankWorkbook(Season $season): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $questions = $spreadsheet->getActiveSheet();
        $questions->setTitle('Questions');
        $this->fillBankQuestionsSheet($questions, $season);

        $labels = $spreadsheet->createSheet();
        $labels->setTitle('Labels');
        $this->fillQuestionLabelsSheet($labels, $season);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function fillBankQuestionsSheet(Worksheet $sheet, Season $season): void
    {
        $metaColumns = ['Question', 'Reusable', 'Complete for quiz', 'Labels', 'Used in quizzes'];
        $sheet->fromArray($metaColumns, null, 'A1');
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $answerStartColumnIndex = \count($metaColumns);
        $maxAnswers = 0;
        $row = 2;

        foreach ($season->bankQuestions as $bankQuestion) {
            $labels = implode(', ', array_map(
                static fn (QuestionLabel $label): string => $label->name,
                $bankQuestion->labels->toArray(),
            ));
            $usedInQuizzes = implode(', ', array_map(
                static fn (BankQuestionUsage $usage): string => $usage->quiz->name,
                $bankQuestion->usages->toArray(),
            ));

            $sheet->fromArray([
                $bankQuestion->question,
                $bankQuestion->reusable ? 'Yes' : 'No',
                $bankQuestion->isCompleteForQuiz ? 'Yes' : 'No',
                $labels,
                $usedInQuizzes,
            ], null, 'A'.$row);

            $col = 0;
            foreach ($bankQuestion->answers as $answer) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($answerStartColumnIndex + 1 + 2 * $col).$row, $answer->text);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($answerStartColumnIndex + 2 + 2 * $col).$row, $answer->isRightAnswer);
                ++$col;
            }

            $maxAnswers = max($maxAnswers, $col);
            ++$row;
        }

        for ($i = 0; $i < $maxAnswers; ++$i) {
            $answerCol = Coordinate::stringFromColumnIndex($answerStartColumnIndex + 1 + 2 * $i);
            $correctCol = Coordinate::stringFromColumnIndex($answerStartColumnIndex + 2 + 2 * $i);

            $sheet->setCellValue($answerCol.'1', 'Answer '.($i + 1));
            $sheet->setCellValue($correctCol.'1', 'Correct');
        }

        $lastColumnIndex = $answerStartColumnIndex + max(1, 2 * $maxAnswers);
        foreach (range('A', Coordinate::stringFromColumnIndex($lastColumnIndex)) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function fillQuestionLabelsSheet(Worksheet $sheet, Season $season): void
    {
        $sheet->fromArray(['Name', 'Colour', 'Slug'], null, 'A1');
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $row = 2;
        foreach ($season->questionLabels as $label) {
            $sheet->fromArray([$label->name, $label->colour->name, $label->slug], null, 'A'.$row);
            ++$row;
        }

        foreach (['A', 'B', 'C'] as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /** @throws FilesystemException */
    private function writeToTempFile(Spreadsheet $spreadsheet): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tvdt_export_sheet_');
        new Writer\Xlsx($spreadsheet)->save($path);

        return $path;
    }

    /** Strips characters that are unsafe in a zip entry name or a downloaded filename (path separators, traversal, control characters). */
    public static function sanitizeForPath(string $value): string
    {
        $sanitized = preg_replace('#[\\\\/:*?"<>|\x00-\x1F]+#', '-', $value);
        $sanitized = mb_trim($sanitized, " .\t\n\r\0\x0B-");

        return '' === $sanitized ? 'unnamed' : $sanitized;
    }
}
