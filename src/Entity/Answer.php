<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $ordering;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private Question $question;

    /** @var Collection<int, Candidate> */
    #[ORM\ManyToMany(targetEntity: Candidate::class, inversedBy: 'answersOnCandidate')]
    private Collection $candidates;

    /** @var Collection<int, GivenAnswer> */
    #[ORM\OneToMany(targetEntity: GivenAnswer::class, mappedBy: 'answer', orphanRemoval: true)]
    private Collection $givenAnswers;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string $text,
        #[ORM\Column]
        private bool $isRightAnswer = false,
    ) {
        $this->candidates = new ArrayCollection();
        $this->givenAnswers = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function isRightAnswer(): bool
    {
        return $this->isRightAnswer;
    }

    public function setRightAnswer(bool $isRightAnswer): static
    {
        $this->isRightAnswer = $isRightAnswer;

        return $this;
    }

    /** @return Collection<int, Candidate> */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

    public function addCandidate(Candidate $candidate): static
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates->add($candidate);
        }

        return $this;
    }

    public function removeCandidate(Candidate $candidate): static
    {
        $this->candidates->removeElement($candidate);

        return $this;
    }

    /** @return Collection<int, GivenAnswer> */
    public function getGivenAnswers(): Collection
    {
        return $this->givenAnswers;
    }

    public function addGivenAnswer(GivenAnswer $givenAnswer): static
    {
        if (!$this->givenAnswers->contains($givenAnswer)) {
            $this->givenAnswers->add($givenAnswer);
            $givenAnswer->setAnswer($this);
        }

        return $this;
    }

    public function getOrdering(): int
    {
        return $this->ordering;
    }

    public function setOrdering(int $ordering): self
    {
        $this->ordering = $ordering;

        return $this;
    }
}
