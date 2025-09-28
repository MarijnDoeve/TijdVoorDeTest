<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\SeasonSettingsRepository;

#[ORM\Entity(repositoryClass: SeasonSettingsRepository::class)]
class SeasonSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $showNumbers = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $confirmAnswers = false;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function isShowNumbers(): bool
    {
        return $this->showNumbers;
    }

    public function setShowNumbers(bool $showNumbers): self
    {
        $this->showNumbers = $showNumbers;

        return $this;
    }

    public function isConfirmAnswers(): bool
    {
        return $this->confirmAnswers;
    }

    public function setConfirmAnswers(bool $confirmAnswers): self
    {
        $this->confirmAnswers = $confirmAnswers;

        return $this;
    }
}
