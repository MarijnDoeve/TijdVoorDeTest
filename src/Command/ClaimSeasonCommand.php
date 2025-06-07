<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:claim-season',
    description: 'Give a user owner rights on a season',
)]
readonly class ClaimSeasonCommand
{
    public function __construct(private UserRepository $userRepository, private SeasonRepository $seasonRepository, private EntityManagerInterface $entityManager) {}

    public function __invoke(
        #[Argument]
        string $seasonCode,
        #[Argument]
        string $email,
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        try {
            $season = $this->seasonRepository->findOneBy(['seasonCode' => $seasonCode]);
            if (null === $season) {
                throw new \InvalidArgumentException('Season not found');
            }

            $user = $this->userRepository->findOneBy(['email' => $email]);
            if (null === $user) {
                throw new \InvalidArgumentException('User not found');
            }

            $season->addOwner($user);

            $this->entityManager->flush();
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $io->error($invalidArgumentException->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
