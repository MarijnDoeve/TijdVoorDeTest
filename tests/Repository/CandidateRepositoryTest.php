<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tvdt\Entity\Candidate;
use Tvdt\Entity\Season;
use Tvdt\Repository\CandidateRepository;
use Tvdt\Repository\SeasonRepository;

class CandidateRepositoryTest extends KernelTestCase
{
    private SeasonRepository $seasonRepository;
    private CandidateRepository $candidateRepository;

    protected function setUp(): void
    {
        $this->seasonRepository = self::getContainer()->get(SeasonRepository::class);
        $this->candidateRepository = self::getContainer()->get(CandidateRepository::class);
    }

    /** @return iterable<string, array{0: string}> */
    public static function candidateHashDataProvider(): iterable
    {
        yield 'Normal' => ['Q2xhdWRpYQ'];
        yield 'lowercase' => ['Y2xhdWRpYQ'];
        yield 'UPPERCASE' => ['Q0xBVURJQQ'];
    }

    #[DataProvider('candidateHashDataProvider')]
    public function testGetCandidateByHash(string $hash): void
    {
        /** @var Season $krtekSeason */
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $candidate = $this->candidateRepository->getCandidateByHash(
            $krtekSeason,
            $hash,
        );

        $this->assertInstanceOf(Candidate::class, $candidate);

        $this->assertSame('Claudia', $candidate->name);
    }

    public function testGetCandidateByHashUnknownHashReturnsNull(): void
    {
        /** @var Season $krtekSeason */
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $result = $this->candidateRepository->getCandidateByHash(
            $krtekSeason,
            'TWFyaWpu',
        );
        $this->assertNull($result);
    }

    public function testGetCandidateByHashInvalidBase64HashReturnsNull(): void
    {
        /** @var Season $krtekSeason */
        $krtekSeason = $this->seasonRepository->findOneBySeasonCode('krtek');
        $result = $this->candidateRepository->getCandidateByHash(
            $krtekSeason,
            'TWFyaWpu*',
        );
        $this->assertNull($result);
    }

    public function testGetScores(): void
    {
        $this->markTestIncomplete('TODO: Make fixtures first and write good test.');
    }
}
