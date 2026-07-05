<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Safe\DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\Backoffice\QuizController;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Question;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;

#[CoversClass(QuizController::class)]
final class QuizControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'krtek-admin@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);
    }

    private function getQuizByName(string $name): Quiz
    {
        $quiz = $this->entityManager->getRepository(Quiz::class)->findOneBy(['name' => $name]);
        $this->assertInstanceOf(Quiz::class, $quiz);

        return $quiz;
    }

    private function getCandidate(string $name): Candidate
    {
        $candidate = $this->entityManager->getRepository(Candidate::class)->findOneBy(['name' => $name]);
        $this->assertInstanceOf(Candidate::class, $candidate);

        return $candidate;
    }

    private function getCsrfTokenFromOverview(Quiz $quiz, string $formActionContains): string
    {
        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/overview', $quiz->id));
        self::assertResponseIsSuccessful();

        $input = $crawler->filter(\sprintf('form[action*="%s"] input[name="_token"]', $formActionContains));
        $this->assertGreaterThan(0, $input->count(), \sprintf('No form found with action containing "%s"', $formActionContains));

        return (string) $input->first()->attr('value');
    }

    public function testIndexRedirectsToOverview(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s', $quiz->id));

        self::assertResponseRedirects(\sprintf('/backoffice/season/krtek/quiz/%s/overview', $quiz->id));
    }

    public function testOverviewLoadsSuccessfully(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/overview', $quiz->id));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Quiz 1');
    }

    public function testResultTabLoadsSuccessfully(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/result', $quiz->id));

        self::assertResponseIsSuccessful();
    }

    public function testCandidatesTabLoadsSuccessfully(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/candidates-list', $quiz->id));

        self::assertResponseIsSuccessful();
    }

    public function testAnswerMappingRedirectsToFirstQuestion(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/answer-mapping', $quiz->id));

        self::assertResponseRedirects();
        $this->assertStringContainsString('/candidates/', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testCandidatesQuestionTabLoadsSuccessfully(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $question = $quiz->questions->first();
        $this->assertInstanceOf(Question::class, $question);

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/candidates/%s', $quiz->id, $question->id));

        self::assertResponseIsSuccessful();
    }

    public function testSaveCandidateAnswersPersistsSelection(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $question = $quiz->questions->first();
        $this->assertInstanceOf(Question::class, $question);
        $answer = $question->answers->first();
        $this->assertInstanceOf(Answer::class, $answer);
        $candidate = $this->getCandidate('Tom');

        $url = \sprintf('/backoffice/season/krtek/quiz/%s/candidates/%s', $quiz->id, $question->id);
        $crawler = $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();
        $token = (string) $crawler->filter('input[name="_token"]')->first()->attr('value');

        $this->client->request(Request::METHOD_POST, $url, [
            '_token' => $token,
            'candidate_answer' => [
                (string) $candidate->id => [(string) $answer->id],
            ],
        ]);

        $this->assertResponseRedirects($url);
        $this->entityManager->clear();

        $savedAnswer = $this->entityManager->getRepository(Answer::class)->find($answer->id);
        $this->assertInstanceOf(Answer::class, $savedAnswer);
        $this->assertTrue($savedAnswer->candidates->exists(
            static fn (int $key, Candidate $c): bool => $c->id->equals($candidate->id),
        ));
    }

    public function testToggleCandidateCreatesInactiveQuizCandidate(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $candidate = $this->getCandidate('Tom');

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/candidates-list', $quiz->id));
        self::assertResponseIsSuccessful();
        $token = (string) $crawler->filter(\sprintf('form[action*="/%s/toggle"] input[name="_token"]', $candidate->id))->first()->attr('value');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/quiz/%s/candidate/%s/toggle', $quiz->id, $candidate->id), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $quizCandidate = $this->entityManager->getRepository(QuizCandidate::class)->findOneBy([
            'quiz' => $this->getQuizByName('Quiz 1'),
            'candidate' => $this->getCandidate('Tom'),
        ]);
        $this->assertInstanceOf(QuizCandidate::class, $quizCandidate);
        $this->assertFalse($quizCandidate->active);
    }

    public function testToggleCandidateTogglesActiveState(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $candidate = $this->getCandidate('Tom');

        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $quizCandidate->active = false;

        $this->entityManager->persist($quizCandidate);
        $this->entityManager->flush();

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/candidates-list', $quiz->id));
        $token = (string) $crawler->filter(\sprintf('form[action*="/%s/toggle"] input[name="_token"]', $candidate->id))->first()->attr('value');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/quiz/%s/candidate/%s/toggle', $quiz->id, $candidate->id), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $updated = $this->entityManager->getRepository(QuizCandidate::class)->findOneBy([
            'quiz' => $this->getQuizByName('Quiz 1'),
            'candidate' => $this->getCandidate('Tom'),
        ]);
        $this->assertInstanceOf(QuizCandidate::class, $updated);
        $this->assertTrue($updated->active);
    }

    public function testModifyCorrection(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $candidate = $this->getCandidate('Tom');

        // getScores() requires started IS NOT NULL and at least one GivenAnswer
        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $quizCandidate->started = new DateTimeImmutable();

        $this->entityManager->persist($quizCandidate);
        $firstQuestion = $quiz->questions->first();
        $this->assertInstanceOf(Question::class, $firstQuestion);
        $answer = $firstQuestion->answers->first();
        $this->assertInstanceOf(Answer::class, $answer);
        $this->entityManager->persist(new GivenAnswer($candidate, $quiz, $answer));
        $this->entityManager->flush();

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/result', $quiz->id));
        self::assertResponseIsSuccessful();
        $token = (string) $crawler->filter(\sprintf('form[action*="%s/modify_correction"] input[name="_token"]', $candidate->id))->first()->attr('value');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/quiz/%s/candidate/%s/modify_correction', $quiz->id, $candidate->id), [
            '_token' => $token,
            'corrections' => '1.5',
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $updated = $this->entityManager->getRepository(QuizCandidate::class)->findOneBy([
            'quiz' => $this->getQuizByName('Quiz 1'),
            'candidate' => $this->getCandidate('Tom'),
        ]);
        $this->assertInstanceOf(QuizCandidate::class, $updated);
        $this->assertEqualsWithDelta(1.5, $updated->corrections, \PHP_FLOAT_EPSILON);
    }

    public function testModifyPenalty(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $candidate = $this->getCandidate('Claudia');

        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $quizCandidate->started = new DateTimeImmutable();

        $this->entityManager->persist($quizCandidate);
        $firstQuestion = $quiz->questions->first();
        $this->assertInstanceOf(Question::class, $firstQuestion);
        $answer = $firstQuestion->answers->first();
        $this->assertInstanceOf(Answer::class, $answer);
        $this->entityManager->persist(new GivenAnswer($candidate, $quiz, $answer));
        $this->entityManager->flush();

        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/result', $quiz->id));
        self::assertResponseIsSuccessful();
        $token = (string) $crawler->filter(\sprintf('form[action*="%s/modify_penalty"] input[name="_token"]', $candidate->id))->first()->attr('value');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/quiz/%s/candidate/%s/modify_penalty', $quiz->id, $candidate->id), [
            '_token' => $token,
            'penalty' => '30',
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $updated = $this->entityManager->getRepository(QuizCandidate::class)->findOneBy([
            'quiz' => $this->getQuizByName('Quiz 1'),
            'candidate' => $this->getCandidate('Claudia'),
        ]);
        $this->assertInstanceOf(QuizCandidate::class, $updated);
        $this->assertSame(30, $updated->penaltySeconds);
    }

    public function testDeleteQuiz(): void
    {
        $quiz = $this->getQuizByName('Quiz 2');
        $quizId = $quiz->id;
        $token = $this->getCsrfTokenFromOverview($quiz, '/delete');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/quiz/%s/delete', $quiz->id), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/backoffice/season/krtek');
        $this->entityManager->clear();
        $this->assertNotInstanceOf(Quiz::class, $this->entityManager->getRepository(Quiz::class)->find($quizId));
    }

    public function testNonOwnerIsDenied(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);

        $quiz = $this->getQuizByName('Quiz 1');
        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/overview', $quiz->id));

        self::assertResponseStatusCodeSame(403);
    }

    public function testOverviewLoadsForEmptyQuiz(): void
    {
        $season = $this->entityManager->getRepository(Season::class)->findOneBy(['seasonCode' => 'krtek']);
        $this->assertInstanceOf(Season::class, $season);

        $emptyQuiz = new Quiz();
        $emptyQuiz->name = 'Empty Quiz';

        $season->addQuiz($emptyQuiz);
        $this->entityManager->persist($emptyQuiz);
        $this->entityManager->flush();

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/overview', $emptyQuiz->id));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Empty Quiz');
    }

    public function testAnswerMappingRedirectsWithFlashWhenNoQuestions(): void
    {
        $season = $this->entityManager->getRepository(Season::class)->findOneBy(['seasonCode' => 'krtek']);
        $this->assertInstanceOf(Season::class, $season);

        $emptyQuiz = new Quiz();
        $emptyQuiz->name = 'Empty Quiz';

        $season->addQuiz($emptyQuiz);
        $this->entityManager->persist($emptyQuiz);
        $this->entityManager->flush();

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/krtek/quiz/%s/answer-mapping', $emptyQuiz->id));

        self::assertResponseRedirects(\sprintf('/backoffice/season/krtek/quiz/%s/overview', $emptyQuiz->id));
        $this->client->followRedirect();
        self::assertSelectorTextContains('body', 'Deze test heeft nog geen vragen');
    }
}
