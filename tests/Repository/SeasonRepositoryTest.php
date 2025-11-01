<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use Tvdt\Entity\Season;
use Tvdt\Repository\SeasonRepository;

#[CoversClass(SeasonRepository::class)]
final class SeasonRepositoryTest extends DatabaseTestCase
{
    public function testGetSeasonsForUser(): void
    {
        $user = $this->getUserByEmail('krtek-admin@example.org');

        $seasons = $this->seasonRepository->getSeasonsForUser($user);
        $this->assertCount(1, $seasons);
        $this->assertSame('krtek', $seasons[0]->seasonCode);

        $user = $this->getUserByEmail('user1@example.org');

        $seasons = $this->seasonRepository->getSeasonsForUser($user);
        $this->assertCount(1, $seasons);
        $this->assertSame('bbbbb', $seasons[0]->seasonCode);
    }

    public function testUserWithMultipleSeasons(): void
    {
        $user = $this->getUserByEmail('user2@example.org');
        $seasons = $this->seasonRepository->getSeasonsForUser($user);

        $this->assertCount(2, $seasons);
        $this->assertSame('bbbbb', $seasons[0]->seasonCode);
        $this->assertSame('krtek', $seasons[1]->seasonCode);
    }

    public function testGetSeasonsForUserWithoutSeasonsReturnsEmpty(): void
    {
        $user = $this->getUserByEmail('test@example.org');

        $seasons = $this->seasonRepository->getSeasonsForUser($user);
        $this->assertEmpty($seasons);
    }

    public function testFindOneBySeasonCode(): void
    {
        $season = $this->seasonRepository->findOneBySeasonCode('krtek');
        $this->assertInstanceOf(Season::class, $season);
        $this->assertSame('krtek', $season->seasonCode);
    }

    public function testFindOneBySeasonCodeUnknownSeasonReturnsNull(): void
    {
        $season = $this->seasonRepository->findOneBySeasonCode('invalid');
        $this->assertNotInstanceOf(Season::class, $season);
    }
}
