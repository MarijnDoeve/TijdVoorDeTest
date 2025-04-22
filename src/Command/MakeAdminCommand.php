<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:make-admin',
    description: 'Give a user the role admin',
)]
class MakeAdminCommand extends Command
{
    public function __construct(private UserRepository $userRepository)
    {
        parent::__construct();
    }





    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the user to make admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        try {
            $this->userRepository->makeAdmin($email);
        } catch (\InvalidArgumentException) {
            $io->error('User not found');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
