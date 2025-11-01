<?php

declare(strict_types=1);

namespace Tvdt\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Tvdt\DataFixtures\TestFixtures;
use Tvdt\Repository\UserRepository;

use function PHPUnit\Framework\assertEmpty;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends DatabaseTestCase
{
    public function testUpgradePassword(): void
    {
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $user = $this->getUserByEmail('user1@example.org');

        $newHash = $passwordHasher->hashPassword($user, TestFixtures::PASSWORD);

        $this->assertNotSame($newHash, $user->password);
        $this->userRepository->upgradePassword($user, $newHash);

        $this->entityManager->refresh($user);
        $this->assertSame($newHash, $user->password);
    }

    public function testMakeAdmin(): void
    {
        $user = $this->getUserByEmail('test@example.org');
        assertEmpty($user->roles);
        $this->userRepository->makeAdmin('test@example.org');
        $this->entityManager->refresh($user);
        $this->assertSame(['ROLE_ADMIN'], $user->roles);
    }

    public function testMakeAdminInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->userRepository->makeAdmin('invalid@example.org');
    }
}
