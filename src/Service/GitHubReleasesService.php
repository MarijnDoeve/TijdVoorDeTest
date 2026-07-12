<?php

declare(strict_types=1);

namespace Tvdt\Service;

use Psr\Cache\InvalidArgumentException;
use Safe\DateTimeImmutable;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class GitHubReleasesService
{
    private const string RELEASES_URL = 'https://api.github.com/repos/MarijnDoeve/TijdVoorDeTest/releases';

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {}

    /**
     * @throws InvalidArgumentException
     *
     * @return list<array{tagName: string, name: string, publishedAt: ?\DateTimeImmutable, body: string, url: string}>
     */
    public function getReleases(): array
    {
        return $this->cache->get('github_releases', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            try {
                $response = $this->httpClient->request('GET', self::RELEASES_URL, [
                    'headers' => [
                        'Accept' => 'application/vnd.github+json',
                        'User-Agent' => 'TijdVoorDeTest-Backoffice',
                    ],
                ]);

                /** @var list<array{tag_name: string, name: ?string, published_at: ?string, body: ?string, html_url: string}> $releases */
                $releases = $response->toArray();
            } catch (ExceptionInterface) {
                return [];
            }

            return array_map(static fn (array $release): array => [
                'tagName' => $release['tag_name'],
                'name' => $release['name'] ?: $release['tag_name'],
                'publishedAt' => $release['published_at'] ? new DateTimeImmutable($release['published_at']) : null,
                'body' => (string) $release['body'],
                'url' => $release['html_url'],
            ], $releases);
        });
    }
}
