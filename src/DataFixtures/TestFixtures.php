<?php

declare(strict_types=1);

namespace Tvdt\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tvdt\DataFixtures\KrtekFixtures;
use Tvdt\Entity\Season;
use Tvdt\Entity\User;

final class TestFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const string PASSWORD = 'test1234';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [KrtekFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->email = 'test@example.org';
        $user->password = $this->passwordHasher->hashPassword($user, self::PASSWORD);

        $manager->persist($user);

        $user = new User();
        $user->email = 'krtek-admin@example.org';
        $user->password = $this->passwordHasher->hashPassword($user, self::PASSWORD);

        $manager->persist($user);

        $krtek = $this->getReference(KrtekFixtures::KRTEK_SEASON, Season::class);
        $krtek->addOwner($user);

        $anotherSeason = new Season();
        $anotherSeason->name = 'Another Season';
        $anotherSeason->seasonCode = 'bbbbb';

        $manager->persist($anotherSeason);
        $this->addReference('another-season', $anotherSeason);

        $user = new User();
        $user->email = 'user1@example.org';
        $user->password = $this->passwordHasher->hashPassword($user, self::PASSWORD);

        $manager->persist($user);
        $user->addSeason($anotherSeason);

        $user = new User();
        $user->email = 'user2@example.org';
        $user->password = $this->passwordHasher->hashPassword($user, self::PASSWORD);

        $manager->persist($user);

        $krtek->addOwner($user);
        $anotherSeason->addOwner($user);

        $manager->flush();
    }
}
