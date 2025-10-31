<?php

declare(strict_types=1);

namespace Tvdt\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Tvdt\Entity\User;
use Tvdt\Repository\UserRepository;

final class MakeAdminCommandTest extends KernelTestCase
{
    private UserRepository $userRepository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $container = self::getContainer();

        \assert(self::$kernel instanceof KernelInterface);

        $this->userRepository = $container->get(UserRepository::class);
        $application = new Application(self::$kernel);
        $command = $application->find('tvdt:make-admin');
        $this->commandTester = new CommandTester($command);
    }

    public function testMakeAdmin(): void
    {
        $this->commandTester->execute([
            'email' => 'test@example.org',
        ]);

        $user = $this->userRepository->findOneBy(['email' => 'test@example.org']);
        \assert($user instanceof User);

        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertContains('ROLE_ADMIN', $user->roles);
    }

    public function testInvalidEmalFails(): void
    {
        $this->commandTester->execute([
            'email' => 'nonexisting@example.org',
        ]);

        $this->assertSame(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->commandTester->getStatusCode();
    }
}
