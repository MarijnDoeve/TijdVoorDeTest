<?php

declare(strict_types=1);

namespace Tvdt\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Tvdt\Entity\Season;
use Tvdt\Repository\SeasonRepository;

final class ClaimSeasonCommandTest extends KernelTestCase
{
    private SeasonRepository $seasonRepository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $container = self::getContainer();

        \assert(self::$kernel instanceof KernelInterface);

        $this->seasonRepository = $container->get(SeasonRepository::class);
        $application = new Application(self::$kernel);
        $command = $application->find('tvdt:claim-season');
        $this->commandTester = new CommandTester($command);
    }

    public function testSeasonClaim(): void
    {
        $this->commandTester->execute([
            'season-code' => 'krtek',
            'email' => 'test@example.org',
        ]);

        $season = $this->seasonRepository->findOneBySeasonCode('krtek');
        \assert($season instanceof Season);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertCount(1, $season->owners);
    }

    public function testInvalidEmalFails(): void
    {
        $this->commandTester->execute([
            'season-code' => 'krtek',
            'email' => 'nonexisting@example.org',
        ]);

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->commandTester->getStatusCode();
    }

    public function testInvalidSeasonCodeFails(): void
    {
        $this->commandTester->execute([
            'season-code' => 'dhadk',
            'email' => 'test@example.org',
        ]);

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->commandTester->getStatusCode();
    }
}
