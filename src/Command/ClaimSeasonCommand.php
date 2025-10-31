<?php

declare(strict_types=1);

namespace Tvdt\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tvdt\Repository\SeasonRepository;
use Tvdt\Repository\UserRepository;

#[AsCommand(
    name: 'tvdt:claim-season',
    description: 'Give a user owner rights on a season',
)]
final readonly class ClaimSeasonCommand
{
    public function __construct(private UserRepository $userRepository, private SeasonRepository $seasonRepository, private EntityManagerInterface $entityManager) {}

    public function __invoke(
        #[Argument]
        string $seasonCode,
        #[Argument]
        string $email,
        SymfonyStyle $io,
    ): int {
        try {
            $season = $this->seasonRepository->findOneBySeasonCode($seasonCode);
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
