<?php

declare(strict_types=1);

namespace Tvdt\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tvdt\Entity\User;

final class DevFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->email = 'admin@tijdvoordetest.nl';
        $user->password = $this->passwordHasher->hashPassword($user, '12345678');
        $user->roles = ['ROLE_ADMIN'];

        $manager->persist($user);

        $manager->flush();
    }
}
