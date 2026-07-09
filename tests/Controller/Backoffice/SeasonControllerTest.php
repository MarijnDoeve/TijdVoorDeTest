<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\Backoffice\SeasonController;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Season;
use Tvdt\Tests\Controller\AbstractControllerWebTestCase;

#[CoversClass(SeasonController::class)]
final class SeasonControllerTest extends AbstractControllerWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAs('krtek-admin@example.org');
    }

    public function testRegenerateSeasonCodeChangesTheCode(): void
    {
        $oldCode = 'krtek';
        $token = $this->getCsrfTokenFromPage(\sprintf('/backoffice/season/%s/settings', $oldCode), '/regenerate-code');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/%s/settings/regenerate-code', $oldCode), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects();
        $this->entityManager->clear();

        $this->assertNotInstanceOf(Season::class, $this->entityManager->getRepository(Season::class)->findOneBy(['seasonCode' => $oldCode]));

        $location = (string) $this->client->getResponse()->headers->get('Location');
        $this->assertMatchesRegularExpression('#^/backoffice/season/[a-z]{5}/settings$#', $location);
    }

    public function testRegenerateSeasonCodeIsDeniedForNonOwner(): void
    {
        $token = $this->getCsrfTokenFromPage('/backoffice/season/krtek/settings', '/regenerate-code');

        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_POST, '/backoffice/season/krtek/settings/regenerate-code', [
            '_token' => $token,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testRenameCandidate(): void
    {
        $candidate = $this->getCandidate('Tom');
        $token = $this->getCsrfTokenFromPage('/backoffice/season/krtek/candidates', \sprintf('/candidate/%s/rename', $candidate->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/candidate/%s/rename', $candidate->id), [
            '_token' => $token,
            'name' => 'Tommy',
        ]);

        self::assertResponseRedirects('/backoffice/season/krtek/candidates');
        $this->entityManager->clear();

        $renamed = $this->entityManager->getRepository(Candidate::class)->find($candidate->id);
        $this->assertInstanceOf(Candidate::class, $renamed);
        $this->assertSame('Tommy', $renamed->name);
    }

    public function testRenameCandidateToExistingNameShowsError(): void
    {
        $candidate = $this->getCandidate('Tom');
        $token = $this->getCsrfTokenFromPage('/backoffice/season/krtek/candidates', \sprintf('/candidate/%s/rename', $candidate->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/candidate/%s/rename', $candidate->id), [
            '_token' => $token,
            'name' => 'Claudia',
        ]);

        self::assertResponseRedirects('/backoffice/season/krtek/candidates');
        $this->entityManager->clear();

        $unchanged = $this->entityManager->getRepository(Candidate::class)->find($candidate->id);
        $this->assertInstanceOf(Candidate::class, $unchanged);
        $this->assertSame('Tom', $unchanged->name);
    }

    public function testDeleteCandidate(): void
    {
        $candidate = $this->getCandidate('Tom');
        $candidateId = $candidate->id;
        $token = $this->getCsrfTokenFromPage('/backoffice/season/krtek/candidates', \sprintf('/candidate/%s/delete', $candidate->id));

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/candidate/%s/delete', $candidate->id), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/backoffice/season/krtek/candidates');
        $this->entityManager->clear();

        $this->assertNotInstanceOf(Candidate::class, $this->entityManager->getRepository(Candidate::class)->find($candidateId));
    }

    public function testRenameCandidateIsDeniedForNonOwner(): void
    {
        $candidate = $this->getCandidate('Tom');
        $token = $this->getCsrfTokenFromPage('/backoffice/season/krtek/candidates', \sprintf('/candidate/%s/rename', $candidate->id));

        $this->loginAs('test@example.org');

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/candidate/%s/rename', $candidate->id), [
            '_token' => $token,
            'name' => 'Tommy',
        ]);

        self::assertResponseStatusCodeSame(403);
    }
}
