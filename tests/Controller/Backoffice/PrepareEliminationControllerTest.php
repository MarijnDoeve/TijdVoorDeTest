<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use PHPUnit\Framework\Attributes\CoversClass;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\Backoffice\PrepareEliminationController;
use Tvdt\Entity\Answer;
use Tvdt\Entity\Elimination;
use Tvdt\Entity\EliminationScreenView;
use Tvdt\Entity\GivenAnswer;
use Tvdt\Entity\Question;
use Tvdt\Entity\QuizCandidate;
use Tvdt\Tests\Controller\AbstractControllerWebTestCase;

#[CoversClass(PrepareEliminationController::class)]
final class PrepareEliminationControllerTest extends AbstractControllerWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAs('krtek-admin@example.org');
    }

    public function testIndexCreatesEliminationAndRedirectsToView(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $candidate = $this->getCandidate('Tom');

        $quizCandidate = new QuizCandidate($quiz, $candidate);
        $quizCandidate->started = new DateTimeImmutable();

        $this->entityManager->persist($quizCandidate);

        $firstQuestion = $quiz->questions->first();
        $this->assertInstanceOf(Question::class, $firstQuestion);
        $answer = $firstQuestion->answers->first();
        $this->assertInstanceOf(Answer::class, $answer);
        $this->entityManager->persist(new GivenAnswer($candidate, $quiz, $answer));
        $this->entityManager->flush();

        $token = $this->getCsrfTokenFromPage(\sprintf('/backoffice/season/krtek/quiz/%s/result', $quiz->id), '/elimination/prepare');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/quiz/%s/elimination/prepare', $quiz->id), [
            '_token' => $token,
        ]);

        $response = $this->client->getResponse();
        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('/backoffice/elimination/', (string) $response->headers->get('Location'));

        $this->entityManager->clear();
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = $this->entityManager->getRepository(Elimination::class)->findOneBy(['quiz' => $quiz]);
        $this->assertInstanceOf(Elimination::class, $elimination);
        $this->assertArrayHasKey('Tom', $elimination->data);
    }

    public function testViewEliminationPageLoads(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/elimination/%s', $elimination->id));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testViewEliminationPageShowsScreenViewLog(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $candidate = $this->getCandidate('Tom');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->persist(new EliminationScreenView($elimination, $candidate, Elimination::SCREEN_GREEN));
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/elimination/%s', $elimination->id));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Tom');
        self::assertSelectorTextContains('table', 'Groen');
    }

    public function testViewEliminationPageHidesScreenViewLogWhenEmpty(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/elimination/%s', $elimination->id));

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('table');
    }

    public function testViewEliminationSavesUpdatedColours(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $token = $this->getTokenFromPage(\sprintf('/backoffice/elimination/%s', $elimination->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/elimination/%s', $elimination->id), [
            '_token' => $token,
            'colour-tom' => Elimination::SCREEN_RED,
            'start' => '0',
        ]);

        self::assertResponseRedirects(\sprintf('/backoffice/elimination/%s', $elimination->id));
        $this->entityManager->clear();

        $updated = $this->entityManager->getRepository(Elimination::class)->find($elimination->id);
        $this->assertInstanceOf(Elimination::class, $updated);
        $this->assertSame(Elimination::SCREEN_RED, $updated->data['Tom']);
    }

    public function testViewEliminationWithStartRedirectsToPublicElimination(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $token = $this->getTokenFromPage(\sprintf('/backoffice/elimination/%s', $elimination->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/elimination/%s', $elimination->id), [
            '_token' => $token,
            'start' => '1',
        ]);

        self::assertResponseRedirects(\sprintf('/elimination/%s', $elimination->id));
    }

    public function testDeleteEliminationRemovesEliminationAndRedirectsToQuiz(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $eliminationId = $elimination->id;

        $token = $this->getCsrfTokenFromPage(\sprintf('/backoffice/elimination/%s', $elimination->id), \sprintf('/backoffice/elimination/%s/delete', $elimination->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/elimination/%s/delete', $elimination->id), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects(\sprintf('/backoffice/season/krtek/quiz/%s', $quiz->id));
        $this->entityManager->clear();

        $this->assertNotInstanceOf(Elimination::class, $this->entityManager->getRepository(Elimination::class)->find($eliminationId));
    }

    public function testDeleteEliminationIsDeniedForNonOwner(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $token = $this->getCsrfTokenFromPage(\sprintf('/backoffice/elimination/%s', $elimination->id), \sprintf('/backoffice/elimination/%s/delete', $elimination->id));

        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/elimination/%s/delete', $elimination->id), [
            '_token' => $token,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testIndexIsDeniedForNonOwner(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $token = $this->getCsrfTokenFromPage(\sprintf('/backoffice/season/krtek/quiz/%s/result', $quiz->id), '/elimination/prepare');

        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/quiz/%s/elimination/prepare', $quiz->id), [
            '_token' => $token,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testViewEliminationIsDeniedForNonOwner(): void
    {
        $quiz = $this->getQuizByName('Quiz 1');
        $elimination = new Elimination($quiz);
        $elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($elimination);
        $this->entityManager->flush();

        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/elimination/%s', $elimination->id));

        self::assertResponseStatusCodeSame(403);
    }
}
