<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CorrectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CorrectionRepository::class)]
#[ORM\UniqueConstraint(columns: ['candidate_id', 'quiz_id'])]
class Correction
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'corrections')]
    #[ORM\JoinColumn(nullable: false)]
    private Candidate $candidate;

    #[ORM\ManyToOne(inversedBy: 'corrections')]
    #[ORM\JoinColumn(nullable: false)]
    private Quiz $quiz;

    #[ORM\Column]
    private float $amount = 0;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(Candidate $candidate): static
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
