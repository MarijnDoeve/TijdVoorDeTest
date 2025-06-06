<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\UniqueConstraint(fields: ['name', 'season'])]
class Quiz
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 64)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private Season $season;

    /** @var Collection<int, Question> */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordering' => 'ASC'])]
    private Collection $questions;

    /** @var Collection<int, QuizCandidate> */
    #[ORM\OneToMany(targetEntity: QuizCandidate::class, mappedBy: 'quiz', orphanRemoval: true)]
    private Collection $candidateData;

    #[ORM\Column(nullable: false, options: ['default' => 1])]
    private int $dropouts = 1;

    /** @var Collection<int, Elimination> */
    #[ORM\OneToMany(targetEntity: Elimination::class, mappedBy: 'quiz', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['created' => 'DESC'])]
    private Collection $eliminations;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->candidateData = new ArrayCollection();
        $this->eliminations = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSeason(): Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): static
    {
        $this->season = $season;

        return $this;
    }

    /** @return Collection<int, Question> */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    /** @return Collection<int, QuizCandidate> */
    public function getCandidateData(): Collection
    {
        return $this->candidateData;
    }

    public function getDropouts(): int
    {
        return $this->dropouts;
    }

    public function setDropouts(int $dropouts): static
    {
        $this->dropouts = $dropouts;

        return $this;
    }

    /** @return Collection<int, Elimination> */
    public function getEliminations(): Collection
    {
        return $this->eliminations;
    }

    public function addElimination(Elimination $elimination): self
    {
        $this->eliminations->add($elimination);

        return $this;
    }
}
