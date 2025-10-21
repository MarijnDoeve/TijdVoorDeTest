<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\QuizCandidateRepository;

#[ORM\Entity(repositoryClass: QuizCandidateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(columns: ['candidate_id', 'quiz_id'])]
final class QuizCandidate
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column]
    public float $corrections = 0;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    public private(set) \DateTimeImmutable $created;

    public function __construct(
        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'candidateData')]
        public Quiz $quiz,

        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'quizData')]
        public Candidate $candidate,
    ) {}

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created = new DateTimeImmutable();
    }
}
