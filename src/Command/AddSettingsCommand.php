<?php

declare(strict_types=1);

namespace Tvdt\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tvdt\Entity\SeasonSettings;
use Tvdt\Repository\SeasonRepository;

#[AsCommand(
    name: 'tvdt:add-settings',
    description: 'Add a short description for your command',
)]
readonly class AddSettingsCommand
{
    public function __construct(private SeasonRepository $seasonRepository, private EntityManagerInterface $entityManager) {}

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->seasonRepository->findAll() as $season) {
            if (null !== $season->settings) {
                continue;
            }

            $io->text('Adding settings to season : '.$season->seasonCode);
            $season->settings = new SeasonSettings();
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
