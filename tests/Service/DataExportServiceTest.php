<?php

declare(strict_types=1);

namespace Tvdt\Tests\Service;

use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\CoversClass;
use Tvdt\Entity\Answer;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Entity\User;
use Tvdt\Service\DataExportService;
use Tvdt\Tests\Repository\DatabaseTestCase;

use function Safe\file_put_contents;
use function Safe\tempnam;
use function Safe\unlink;

#[CoversClass(DataExportService::class)]
final class DataExportServiceTest extends DatabaseTestCase
{
    private DataExportService $subject;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = self::getContainer()->get(DataExportService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function testExportForUserWithNoSeasonsContainsOnlyProfile(): void
    {
        $zip = $this->openZip($this->getUserByEmail('test@example.org'));

        $this->assertSame(1, $zip->numFiles);
        $this->assertNotFalse($zip->locateName('profile.xlsx'));
        $zip->close();
    }

    public function testExportForUserIncludesOwnedSeasonsQuizzesAndCandidates(): void
    {
        $zip = $this->openZip($this->getUserByEmail('user2@example.org'));

        $names = $this->entryNames($zip);

        $this->assertContains('profile.xlsx', $names);
        $this->assertContains('krtek-Krtek-Weekend/Quiz-1.xlsx', $names);
        $this->assertContains('krtek-Krtek-Weekend/Quiz-2.xlsx', $names);
        $this->assertContains('krtek-Krtek-Weekend/candidates.xlsx', $names);
        $this->assertContains('krtek-Krtek-Weekend/question-bank.xlsx', $names);
        $this->assertContains('bbbbb-Another-Season/candidates.xlsx', $names);
        $this->assertContains('bbbbb-Another-Season/question-bank.xlsx', $names);

        // Another Season has no quizzes, so no quiz xlsx should be present for it.
        foreach ($names as $name) {
            $this->assertStringStartsNotWith('bbbbb-Another-Season/Quiz', $name);
        }

        $quizContent = $zip->getFromName('krtek-Krtek-Weekend/Quiz-1.xlsx');
        $this->assertIsString($quizContent);
        $this->assertSame(['Quiz info', 'Questions', 'Raw answers', 'Results', 'Eliminations'], $this->sheetNames($quizContent));

        $candidatesContent = $zip->getFromName('krtek-Krtek-Weekend/candidates.xlsx');
        $this->assertIsString($candidatesContent);
        $this->assertSame(['Candidates', 'Season info'], $this->sheetNames($candidatesContent));

        $questionBankContent = $zip->getFromName('krtek-Krtek-Weekend/question-bank.xlsx');
        $this->assertIsString($questionBankContent);
        $this->assertSame(['Questions', 'Labels'], $this->sheetNames($questionBankContent));

        $zip->close();
    }

    public function testQuestionBankSheetIncludesBankQuestionsAndUsage(): void
    {
        $zip = $this->openZip($this->getUserByEmail('user2@example.org'));

        $questionBankContent = $zip->getFromName('krtek-Krtek-Weekend/question-bank.xlsx');
        $this->assertIsString($questionBankContent);
        $zip->close();

        $rows = $this->loadSheet($questionBankContent, 'Questions')->toArray();
        $header = $rows[0];
        $dataRows = \array_slice($rows, 1);

        $questionIndex = array_search('Question', $header, true);
        $reusableIndex = array_search('Reusable', $header, true);
        $labelsIndex = array_search('Labels', $header, true);
        $usedInQuizzesIndex = array_search('Used in quizzes', $header, true);

        $reusableRow = current(array_filter($dataRows, static fn (array $row): bool => 'Wie is de Krtek?' === $row[$questionIndex]));
        $this->assertIsArray($reusableRow);
        $this->assertSame('Yes', $reusableRow[$reusableIndex]);
        $this->assertSame('Finale', $reusableRow[$labelsIndex]);

        $usedRow = current(array_filter($dataRows, static fn (array $row): bool => 'Waar sliep de Krtek?' === $row[$questionIndex]));
        $this->assertIsArray($usedRow);
        $this->assertSame('Quiz 2', $usedRow[$usedInQuizzesIndex]);

        $labelRows = $this->loadSheet($questionBankContent, 'Labels')->toArray();
        $labelNames = array_column(\array_slice($labelRows, 1), 0);
        $this->assertContains('Locatie', $labelNames);
        $this->assertContains('Finale', $labelNames);
    }

    public function testProfileSheetDoesNotContainPasswordHash(): void
    {
        $user = $this->getUserByEmail('user2@example.org');
        $zip = $this->openZip($user);

        $profileContent = $zip->getFromName('profile.xlsx');
        $this->assertIsString($profileContent);
        $zip->close();

        $rows = $this->loadSheet($profileContent, 'Account')->toArray();
        $flattened = implode(' ', array_merge(...$rows));

        $this->assertStringNotContainsString($user->password, $flattened);
    }

    public function testResultsSheetIncludesSoftDeletedQuizCandidates(): void
    {
        $season = $this->getSeasonByCode('krtek');
        $quiz = $this->entityManager->getRepository(Quiz::class)->findOneBy(['name' => 'Quiz 1', 'season' => $season]);
        $this->assertInstanceOf(Quiz::class, $quiz);
        $candidate = $this->getCandidateBySeasonAndName($season, 'Claudia');

        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $this->entityManager->persist($quizCandidate);
        $this->entityManager->flush();

        $this->entityManager->remove($quizCandidate);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $zip = $this->openZip($this->getUserByEmail('user2@example.org'));
        $quizContent = $zip->getFromName('krtek-Krtek-Weekend/Quiz-1.xlsx');
        $this->assertIsString($quizContent);
        $zip->close();

        $rows = $this->loadSheet($quizContent, 'Results')->toArray();
        $deletedColumnIndex = array_search('Deleted', $rows[0], true);
        $this->assertIsInt($deletedColumnIndex);
        $hasDeletedRow = array_any(\array_slice($rows, 1), static fn (array $row): bool => null !== $row[$deletedColumnIndex] && '' !== $row[$deletedColumnIndex]);

        $this->assertTrue($hasDeletedRow, 'Expected the soft-deleted QuizCandidate to still appear with a Deleted timestamp');
    }

    public function testRawAnswersSheetShowsCandidatesByQuestionsGrid(): void
    {
        $season = $this->getSeasonByCode('krtek');
        $quiz = $this->entityManager->getRepository(Quiz::class)->findOneBy(['name' => 'Quiz 1', 'season' => $season]);
        $this->assertInstanceOf(Quiz::class, $quiz);
        $candidate = $this->getCandidateBySeasonAndName($season, 'Claudia');

        /** @var Question $firstQuestion */
        $firstQuestion = $quiz->questions->first();
        $chosenAnswer = $firstQuestion->answers->filter(static fn (Answer $answer): bool => 'Man' === $answer->text)->first();
        $this->assertInstanceOf(Answer::class, $chosenAnswer);

        $this->quizCandidateRepository->createIfNotExist($quiz, $candidate);

        $givenAnswer = new GivenAnswer($candidate, $quiz, $chosenAnswer);
        $this->entityManager->persist($givenAnswer);
        $this->entityManager->flush();

        $zip = $this->openZip($this->getUserByEmail('user2@example.org'));
        $quizContent = $zip->getFromName('krtek-Krtek-Weekend/Quiz-1.xlsx');
        $this->assertIsString($quizContent);
        $zip->close();

        $rows = $this->loadSheet($quizContent, 'Raw answers')->toArray();
        $header = $rows[0];
        $this->assertSame('Candidate', $header[0]);

        $questionColumnIndex = array_search($firstQuestion->question, $header, true);
        $this->assertIsInt($questionColumnIndex);

        $claudiaRow = current(array_filter(
            \array_slice($rows, 1),
            static fn (array $row): bool => 'Claudia' === $row[0],
        ));
        $this->assertIsArray($claudiaRow);
        $this->assertSame('Man', $claudiaRow[$questionColumnIndex]);
    }

    public function testQuizInfoSheetShowsDropoutsFinalizationAndDisabledQuestions(): void
    {
        $season = $this->getSeasonByCode('krtek');
        $quiz = $this->entityManager->getRepository(Quiz::class)->findOneBy(['name' => 'Quiz 1', 'season' => $season]);
        $this->assertInstanceOf(Quiz::class, $quiz);
        $this->assertTrue($quiz->isFinalized);

        /** @var Question $disabledQuestion */
        $disabledQuestion = $quiz->questions->first();
        $disabledQuestion->enabled = false;

        $this->entityManager->flush();

        $zip = $this->openZip($this->getUserByEmail('user2@example.org'));
        $quizContent = $zip->getFromName('krtek-Krtek-Weekend/Quiz-1.xlsx');
        $this->assertIsString($quizContent);
        $zip->close();

        $rows = $this->loadSheet($quizContent, 'Quiz info')->toArray();
        $values = [];
        foreach ($rows as $row) {
            $values[$row[0]] = $row[1];
        }

        $this->assertSame('Quiz 1', $values['Quiz name']);
        $this->assertSame($quiz->dropouts, (int) $values['Number of dropouts']);
        $this->assertSame('Yes', $values['Finalized']);
        $this->assertNotEmpty($values['Finalized at']);
        $this->assertStringContainsString($disabledQuestion->question, (string) $values['Disabled questions']);
    }

    private function openZip(User $user): \ZipArchive
    {
        $zipPath = $this->subject->exportForUser($user);
        $this->tempFiles[] = $zipPath;

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath));

        return $zip;
    }

    /** @return list<string> */
    private function entryNames(\ZipArchive $zip): array
    {
        $names = [];
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);
            $this->assertIsString($name);
            $names[] = $name;
        }

        return $names;
    }

    /** @return list<string> */
    private function sheetNames(string $xlsxContent): array
    {
        $path = $this->createTempPath();
        file_put_contents($path, $xlsxContent);

        return array_values(new Reader\Xlsx()->load($path)->getSheetNames());
    }

    private function loadSheet(string $xlsxContent, string $sheetName): Worksheet
    {
        $path = $this->createTempPath();
        file_put_contents($path, $xlsxContent);

        $sheet = new Reader\Xlsx()->load($path)->getSheetByName($sheetName);
        $this->assertInstanceOf(Worksheet::class, $sheet);

        return $sheet;
    }

    private function createTempPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tvdt_export_test_');
        $this->tempFiles[] = $path;

        return $path;
    }
}
