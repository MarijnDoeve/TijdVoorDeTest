<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\EliminationController;
use Tvdt\Entity\Elimination;
use Tvdt\Helpers\Base64;

#[CoversClass(EliminationController::class)]
final class EliminationControllerTest extends AbstractControllerWebTestCase
{
    private Elimination $elimination;

    protected function setUp(): void
    {
        parent::setUp();

        $quiz = $this->getQuizByName('Quiz 1');

        $this->elimination = new Elimination($quiz);
        $this->elimination->data = ['Tom' => Elimination::SCREEN_GREEN];

        $this->entityManager->persist($this->elimination);
        $this->entityManager->flush();

        $this->loginAs('krtek-admin@example.org');
    }

    public function testIndexIsDeniedForNonOwner(): void
    {
        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_GET, \sprintf('/elimination/%s', $this->elimination->id));

        self::assertResponseStatusCodeSame(403);
    }

    public function testIndexPageLoads(): void
    {
        $this->client->request(Request::METHOD_GET, \sprintf('/elimination/%s', $this->elimination->id));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testIndexRedirectsToCandidateScreen(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/elimination/%s', $this->elimination->id));
        $form = $crawler->filter('form')->form([
            'elimination_enter_name[name]' => 'Tom',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects(\sprintf('/elimination/%s/%s', $this->elimination->id, Base64::base64UrlEncode('Tom')));
    }

    public function testCandidateScreenUnknownCandidateRedirectsWithFlash(): void
    {
        $this->client->request(Request::METHOD_GET, \sprintf('/elimination/%s/%s', $this->elimination->id, Base64::base64UrlEncode('Nobody')));

        self::assertResponseRedirects(\sprintf('/elimination/%s', $this->elimination->id));
        $this->client->followRedirect();
        self::assertSelectorTextContains('body', 'Kon kandidaat met naam Nobody niet vinden');
    }

    public function testCandidateScreenCandidateNotInEliminationDataRedirectsWithFlash(): void
    {
        $this->client->request(Request::METHOD_GET, \sprintf('/elimination/%s/%s', $this->elimination->id, Base64::base64UrlEncode('Claudia')));

        self::assertResponseRedirects(\sprintf('/elimination/%s', $this->elimination->id));
        $this->client->followRedirect();
        self::assertSelectorTextContains('body', 'Kon geen kandidaat vinden met de naam Claudia in de eliminatie');
    }

    public function testCandidateScreenRendersColour(): void
    {
        $this->client->request(Request::METHOD_GET, \sprintf('/elimination/%s/%s', $this->elimination->id, Base64::base64UrlEncode('Tom')));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists(\sprintf('#%s', Elimination::SCREEN_GREEN));
    }
}
