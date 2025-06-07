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
    private Uuid $id;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $created;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        #[ORM\JoinColumn(nullable: false)]
        private Candidate $candidate,

        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Quiz $quiz,

        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        #[ORM\JoinColumn(nullable: false)]
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
