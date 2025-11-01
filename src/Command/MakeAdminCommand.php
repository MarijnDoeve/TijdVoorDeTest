<?php

declare(strict_types=1);

namespace Tvdt\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tvdt\Repository\UserRepository;

#[AsCommand(
    name: 'tvdt:make-admin',
    description: 'Give a user the role admin',
)]
final readonly class MakeAdminCommand
{
    public function __construct(private UserRepository $userRepository) {}

    public function __invoke(
        #[Argument]
        string $email,
        SymfonyStyle $io,
    ): int {
        try {
            $this->userRepository->makeAdmin($email);
        } catch (\InvalidArgumentException) {
            $io->error('User not found');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
