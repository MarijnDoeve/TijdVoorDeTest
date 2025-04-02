<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CandidateRepository;
use App\Repository\QuizRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-command',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    public function __construct(private readonly CandidateRepository $candidateRepository, private readonly QuizRepository $quizRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        new SymfonyStyle($input, $output);

        dd($this->candidateRepository->getScores($this->quizRepository->find('1f00ff44-6f12-630e-9b87-67e78e97c05e')));

        return Command::SUCCESS;
    }
}
