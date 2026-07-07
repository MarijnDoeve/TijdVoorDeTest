<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;
use Tvdt\Repository\ResetPasswordRequestRepository;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    public function __construct(#[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne]
        private User $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
