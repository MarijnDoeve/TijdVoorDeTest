<?php

declare(strict_types=1);

namespace Tvdt\Tests\Controller\Backoffice;

use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Tvdt\Entity\Quiz;
use Tvdt\Entity\User;

/**
 * Guards against N+1 regressions: every backoffice page must run a
 * constant number of queries, independent of the amount of data.
 */
#[CoversNothing]
final class QueryCountTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'krtek-admin@example.org']);
        $this->assertInstanceOf(User::class, $user);
        $this->client->loginUser($user);
    }

    /** @return \Generator<string, array{string, int}> */
    public static function pageProvider(): \Generator
    {
        yield 'season tests tab' => ['/backoffice/season/krtek', 4];
        yield 'question bank tab' => ['/backoffice/season/krtek/question-bank', 6];
        yield 'candidates tab' => ['/backoffice/season/krtek/candidates', 4];
        yield 'settings tab' => ['/backoffice/season/krtek/settings', 4];
        yield 'question bank new' => ['/backoffice/season/krtek/question-bank/new', 4];
        yield 'quiz overview' => ['/backoffice/season/krtek/quiz/%quiz%/overview', 5];
        yield 'quiz result' => ['/backoffice/season/krtek/quiz/%quiz%/result', 6];
        yield 'quiz candidates list' => ['/backoffice/season/krtek/quiz/%quiz%/candidates-list', 7];
    }

    #[DataProvider('pageProvider')]
    public function testPageStaysWithinQueryBudget(string $url, int $maxQueries): void
    {
        if (str_contains($url, '%quiz%')) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $quiz = $entityManager->getRepository(Quiz::class)->findOneBy(['name' => 'Quiz 1']);
            $this->assertInstanceOf(Quiz::class, $quiz);
            $url = str_replace('%quiz%', (string) $quiz->id, $url);
        }

        // Warm up so boot/setup queries do not pollute the profiled request
        $this->client->request(Request::METHOD_GET, '/backoffice');

        $this->client->enableProfiler();
        $this->client->request(Request::METHOD_GET, $url);
        $this->assertResponseIsSuccessful();

        $profile = $this->client->getProfile();
        $this->assertInstanceOf(Profile::class, $profile);

        $collector = $profile->getCollector('db');
        $this->assertInstanceOf(DoctrineDataCollector::class, $collector);
        $queries = $collector->getQueries()['default'] ?? [];

        $sql = implode("\n", array_map(static fn (array $query): string => $query['sql'], $queries));
        $this->assertLessThanOrEqual($maxQueries, \count($queries), \sprintf("Query budget exceeded for %s:\n%s", $url, $sql));
    }
}
