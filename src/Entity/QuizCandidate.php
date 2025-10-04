<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\QuizCandidateRepository;

#[ORM\Entity(repositoryClass: QuizCandidateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(columns: ['candidate_id', 'quiz_id'])]
class QuizCandidate
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private Uuid $id;

    #[ORM\Column]
    private float $corrections = 0;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $created;

    public function __construct(
        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'candidateData')]
        private Quiz $quiz,

        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'quizData')]
        private Candidate $candidate,
    ) {}

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCandidate(): Candidate
    {
        return $this->candidate;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    public function getCorrections(): ?float
    {
        return $this->corrections;
    }

    public function setCorrections(float $corrections): static
    {
        $this->corrections = $corrections;

        return $this;
    }

    public function getCreated(): \DateTimeImmutable
    {
        return $this->created;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created = new DateTimeImmutable();
    }
}
