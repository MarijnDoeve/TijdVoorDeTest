<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\Backoffice\QuestionBankController;
use Tvdt\Entity\BankQuestion;
use Tvdt\Entity\Question;
use Tvdt\Entity\QuestionLabel;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\User;

#[CoversClass(QuestionBankController::class)]
final class QuestionBankControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    private function loginAsOwner(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'krtek-admin@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);
    }

    private function getBankQuestion(string $question): BankQuestion
    {
        $bankQuestion = $this->entityManager->getRepository(BankQuestion::class)->findOneBy(['question' => $question]);
        $this->assertInstanceOf(BankQuestion::class, $bankQuestion);

        return $bankQuestion;
    }

    private function getQuizByName(string $name): Quiz
    {
        $quiz = $this->entityManager->getRepository(Quiz::class)->findOneBy(['name' => $name]);
        $this->assertInstanceOf(Quiz::class, $quiz);

        return $quiz;
    }

    private function getCsrfToken(string $formActionContains): string
    {
        $crawler = $this->client->getCrawler();
        $input = $crawler->filter(\sprintf('form[action*="%s"] input[name="_token"]', $formActionContains));
        $this->assertGreaterThan(0, $input->count(), \sprintf('No form found with action containing "%s"', $formActionContains));

        return (string) $input->first()->attr('value');
    }

    public function testIndexListsBankQuestions(): void
    {
        $this->loginAsOwner();
        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Wie is de Krtek?');
        $this->assertSelectorTextContains('body', 'Waar sliep de Krtek?');
        $this->assertSelectorTextContains('body', 'Wat at de Krtek als ontbijt?');
    }

    public function testIndexFiltersByLabel(): void
    {
        $this->loginAsOwner();
        $label = $this->entityManager->getRepository(QuestionLabel::class)->findOneBy(['name' => 'Locatie']);
        $this->assertInstanceOf(QuestionLabel::class, $label);

        $crawler = $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank?label='.$label->slug);

        $this->assertResponseIsSuccessful();
        $body = $crawler->filter('tbody')->text();
        $this->assertStringContainsString('Waar sliep de Krtek?', $body);
        $this->assertStringNotContainsString('Wie is de Krtek?', $body);
    }

    public function testNonOwnerIsDenied(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateBankQuestion(): void
    {
        $this->loginAsOwner();
        $crawler = $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank/new');
        $this->assertResponseIsSuccessful();

        $token = (string) $crawler->filter('input[name="bank_question_form[_token]"]')->attr('value');

        $this->client->request(Request::METHOD_POST, '/backoffice/season/krtek/question-bank/new', [
            'bank_question_form' => [
                'question' => 'Wat is de lievelingskleur van de Krtek?',
                'reusable' => '1',
                'answers' => [
                    ['text' => 'Rood', 'isRightAnswer' => '1'],
                    ['text' => 'Blauw'],
                ],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $bankQuestion = $this->getBankQuestion('Wat is de lievelingskleur van de Krtek?');
        $this->assertTrue($bankQuestion->reusable);
        $this->assertCount(2, $bankQuestion->answers);
        $this->assertSame('Rood', (string) $bankQuestion->answers->first());
    }

    public function testCreateRefusedWithoutCorrectAnswer(): void
    {
        $this->loginAsOwner();
        $crawler = $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank/new');
        $token = (string) $crawler->filter('input[name="bank_question_form[_token]"]')->attr('value');

        $this->client->request(Request::METHOD_POST, '/backoffice/season/krtek/question-bank/new', [
            'bank_question_form' => [
                'question' => 'Vraag zonder goed antwoord',
                'answers' => [
                    ['text' => 'Een'],
                    ['text' => 'Twee'],
                ],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseIsUnprocessable();
        $this->assertNotInstanceOf(BankQuestion::class, $this->entityManager->getRepository(BankQuestion::class)->findOneBy(['question' => 'Vraag zonder goed antwoord']));
    }

    public function testEditBankQuestion(): void
    {
        $this->loginAsOwner();
        $bankQuestion = $this->getBankQuestion('Wat at de Krtek als ontbijt?');

        $url = \sprintf('/backoffice/season/krtek/question-bank/%s/edit', $bankQuestion->id);
        $crawler = $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseIsSuccessful();
        $token = (string) $crawler->filter('input[name="bank_question_form[_token]"]')->attr('value');

        $this->client->request(Request::METHOD_POST, $url, [
            'bank_question_form' => [
                'question' => 'Wat dronk de Krtek als ontbijt?',
                'answers' => [
                    ['text' => 'Koffie', 'isRightAnswer' => '1'],
                    ['text' => 'Thee'],
                ],
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $bankQuestion = $this->getBankQuestion('Wat dronk de Krtek als ontbijt?');
        $this->assertFalse($bankQuestion->reusable);
        $this->assertCount(2, $bankQuestion->answers);
    }

    public function testDeleteUsedBankQuestionLeavesQuizIntact(): void
    {
        $this->loginAsOwner();
        $bankQuestion = $this->getBankQuestion('Waar sliep de Krtek?');
        $quiz2QuestionCount = $this->getQuizByName('Quiz 2')->questions->count();

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');
        $token = $this->getCsrfToken(\sprintf('%s/delete', $bankQuestion->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/question-bank/%s/delete', $bankQuestion->id), [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $this->assertNotInstanceOf(BankQuestion::class, $this->entityManager->getRepository(BankQuestion::class)->findOneBy(['question' => 'Waar sliep de Krtek?']));
        $this->assertCount($quiz2QuestionCount, $this->getQuizByName('Quiz 2')->questions);
    }

    public function testAssignCopiesQuestionIntoQuiz(): void
    {
        $this->loginAsOwner();
        $bankQuestion = $this->getBankQuestion('Wat at de Krtek als ontbijt?');
        $quiz = $this->getQuizByName('Quiz 2');
        $questionCount = $quiz->questions->count();

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');
        $token = $this->getCsrfToken(\sprintf('%s/assign', $bankQuestion->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/question-bank/%s/assign', $bankQuestion->id), [
            '_token' => $token,
            'quiz' => (string) $quiz->id,
        ]);

        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $quiz = $this->getQuizByName('Quiz 2');
        $this->assertCount($questionCount + 1, $quiz->questions);

        $copiedQuestion = null;
        $maxOrdering = 0;
        foreach ($quiz->questions as $question) {
            $maxOrdering = max($maxOrdering, $question->ordering);
            if ('Wat at de Krtek als ontbijt?' === $question->question) {
                $copiedQuestion = $question;
            }
        }

        $this->assertInstanceOf(Question::class, $copiedQuestion);
        $this->assertSame($maxOrdering, $copiedQuestion->ordering);
        $this->assertCount(3, $copiedQuestion->answers);

        $bankQuestion = $this->getBankQuestion('Wat at de Krtek als ontbijt?');
        $this->assertTrue($bankQuestion->isUsed);
        $this->assertFalse($bankQuestion->canBeAssigned);
    }

    public function testAssignUsedNonReusableQuestionIsRefused(): void
    {
        $this->loginAsOwner();
        $bankQuestion = $this->getBankQuestion('Waar sliep de Krtek?');
        $quiz = $this->getQuizByName('Quiz 2');
        $questionCount = $quiz->questions->count();

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');

        // The assign form is not rendered for used questions, so post with another form's token
        $token = $this->getCsrfToken('/assign');
        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/question-bank/%s/assign', $bankQuestion->id), [
            '_token' => $token,
            'quiz' => (string) $quiz->id,
        ]);

        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $this->assertCount($questionCount, $this->getQuizByName('Quiz 2')->questions);
    }

    public function testAssignSameReusableQuestionTwiceToSameQuizIsRefused(): void
    {
        $this->loginAsOwner();
        $bankQuestion = $this->getBankQuestion('Wie is de Krtek?');
        $quiz = $this->getQuizByName('Quiz 2');
        $questionCount = $quiz->questions->count();

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');
        $token = $this->getCsrfToken(\sprintf('%s/assign', $bankQuestion->id));

        $url = \sprintf('/backoffice/season/krtek/question-bank/%s/assign', $bankQuestion->id);
        $this->client->request(Request::METHOD_POST, $url, ['_token' => $token, 'quiz' => (string) $quiz->id]);
        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->client->request(Request::METHOD_POST, $url, ['_token' => $token, 'quiz' => (string) $quiz->id]);
        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $this->assertCount($questionCount + 1, $this->getQuizByName('Quiz 2')->questions);
    }

    public function testAssignIntoFinalizedQuizIsDenied(): void
    {
        $this->loginAsOwner();
        $bankQuestion = $this->getBankQuestion('Wie is de Krtek?');
        $finalizedQuiz = $this->getQuizByName('Quiz 1');
        $this->assertTrue($finalizedQuiz->isFinalized);

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');
        $token = $this->getCsrfToken(\sprintf('%s/assign', $bankQuestion->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/question-bank/%s/assign', $bankQuestion->id), [
            '_token' => $token,
            'quiz' => (string) $finalizedQuiz->id,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAddAndDeleteLabel(): void
    {
        $this->loginAsOwner();
        $crawler = $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');
        $token = (string) $crawler->filter('form[action$="/question-bank/labels"] input[name="_token"]')->attr('value');

        $this->client->request(Request::METHOD_POST, '/backoffice/season/krtek/question-bank/labels', [
            '_token' => $token,
            'name' => 'Opdracht',
        ]);
        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $label = $this->entityManager->getRepository(QuestionLabel::class)->findOneBy(['name' => 'Opdracht']);
        $this->assertInstanceOf(QuestionLabel::class, $label);

        $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/question-bank');
        $deleteToken = $this->getCsrfToken(\sprintf('labels/%s/delete', $label->slug));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/question-bank/labels/%s/delete', $label->slug), [
            '_token' => $deleteToken,
        ]);
        $this->assertResponseRedirects('/backoffice/season/krtek/question-bank');

        $this->entityManager->clear();
        $this->assertNotInstanceOf(QuestionLabel::class, $this->entityManager->getRepository(QuestionLabel::class)->findOneBy(['name' => 'Opdracht']));
    }
}
