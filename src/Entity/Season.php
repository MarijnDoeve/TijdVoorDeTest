<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 64)]
    private string $name;

    #[ORM\Column(length: 5)]
    private string $seasonCode;

    #[ORM\Column]
    private bool $preregisterCandidates;

    /** @var Collection<int, Quiz> */
    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'season', cascade: ['persist'], orphanRemoval: true)]
    private Collection $quizzes;

    /** @var Collection<int, Candidate> */
    #[ORM\OneToMany(targetEntity: Candidate::class, mappedBy: 'season', cascade: ['persist'], orphanRemoval: true)]
    private Collection $candidates;

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'seasons')]
    private Collection $owners;

    #[ORM\ManyToOne]
    private ?Quiz $ActiveQuiz = null;

    public function __construct()
    {
        $this->quizzes = new ArrayCollection();
        $this->candidates = new ArrayCollection();
        $this->owners = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSeasonCode(): ?string
    {
        return $this->seasonCode;
    }

    public function setSeasonCode(string $seasonCode): static
    {
        $this->seasonCode = $seasonCode;

        return $this;
    }

    public function isPreregisterCandidates(): bool
    {
        return $this->preregisterCandidates;
    }

    public function setPreregisterCandidates(bool $preregisterCandidates): static
    {
        $this->preregisterCandidates = $preregisterCandidates;

        return $this;
    }

    /** @return Collection<int, Quiz> */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setSeason($this);
        }

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
            $candidate->setSeason($this);
        }

        return $this;
    }

    /** @return Collection<int, User> */
    public function getOwners(): Collection
    {
        return $this->owners;
    }

    public function addOwner(User $owner): static
    {
        if (!$this->owners->contains($owner)) {
            $this->owners->add($owner);
        }

        return $this;
    }

    public function removeOwner(User $owner): static
    {
        $this->owners->removeElement($owner);

        return $this;
    }

    public function getActiveQuiz(): ?Quiz
    {
        return $this->ActiveQuiz;
    }

    public function setActiveQuiz(?Quiz $ActiveQuiz): static
    {
        $this->ActiveQuiz = $ActiveQuiz;

        return $this;
    }
}
