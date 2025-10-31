<?php

declare(strict_types=1);

namespace Tvdt\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tvdt\Entity\User;

final class TestFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->email = 'test@example.org';
        $user->password = $this->passwordHasher->hashPassword($user, 'test1234');

        $manager->persist($user);
        $manager->flush();
    }
}
