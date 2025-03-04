<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GivenAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GivenAnswerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GivenAnswer
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private Candidate $candidate;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Quiz $quiz;

    #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Answer $answer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTimeInterface $created;

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

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function setAnswer(?Answer $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created = new DateTimeImmutable();
    }
}
