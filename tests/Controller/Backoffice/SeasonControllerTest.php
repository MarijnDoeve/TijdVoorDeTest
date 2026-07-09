<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tvdt\Controller\Backoffice\SeasonController;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;

#[CoversClass(SeasonController::class)]
final class SeasonControllerTest extends WebTestCase
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

    public function testRegenerateSeasonCodeChangesTheCode(): void
    {
        $oldCode = 'krtek';
        $crawler = $this->client->request(Request::METHOD_GET, \sprintf('/backoffice/season/%s/settings', $oldCode));
        self::assertResponseIsSuccessful();

        $token = (string) $crawler->filter('form[action*="/regenerate-code"] input[name="_token"]')->first()->attr('value');

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
        $crawler = $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/settings');
        self::assertResponseIsSuccessful();
        $token = (string) $crawler->filter('form[action*="/regenerate-code"] input[name="_token"]')->first()->attr('value');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_POST, '/backoffice/season/krtek/settings/regenerate-code', [
            '_token' => $token,
        ]);

        self::assertResponseStatusCodeSame(403);
    }

    private function getCandidate(string $name): Candidate
    {
        $candidate = $this->entityManager->getRepository(Candidate::class)->findOneBy(['name' => $name]);
        $this->assertInstanceOf(Candidate::class, $candidate);

        return $candidate;
    }

    private function getCsrfTokenFromCandidatesTab(string $formActionContains): string
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/backoffice/season/krtek/candidates');
        self::assertResponseIsSuccessful();

        $input = $crawler->filter(\sprintf('form[action*="%s"] input[name="_token"]', $formActionContains));
        $this->assertGreaterThan(0, $input->count(), \sprintf('No form found with action containing "%s"', $formActionContains));

        return (string) $input->first()->attr('value');
    }

    public function testRenameCandidate(): void
    {
        $candidate = $this->getCandidate('Tom');
        $token = $this->getCsrfTokenFromCandidatesTab(\sprintf('/candidate/%s/rename', $candidate->id));

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
        $token = $this->getCsrfTokenFromCandidatesTab(\sprintf('/candidate/%s/rename', $candidate->id));

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
        $token = $this->getCsrfTokenFromCandidatesTab(\sprintf('/candidate/%s/delete', $candidate->id));

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
        $token = $this->getCsrfTokenFromCandidatesTab(\sprintf('/candidate/%s/rename', $candidate->id));

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_POST, \sprintf('/backoffice/season/krtek/candidate/%s/rename', $candidate->id), [
            '_token' => $token,
            'name' => 'Tommy',
        ]);

        self::assertResponseStatusCodeSame(403);
    }
}
