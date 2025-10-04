<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\GivenAnswerRepository;

#[ORM\Entity(repositoryClass: GivenAnswerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class GivenAnswer
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private Uuid $id;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $created;

    public function __construct(
        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        private Candidate $candidate,

        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        #[ORM\ManyToOne]
        private Quiz $quiz,

        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        private Answer $answer,
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

    public function getAnswer(): Answer
    {
        return $this->answer;
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
