<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\QuizRepository;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\UniqueConstraint(fields: ['name', 'season'])]
final class Quiz
{
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column(length: 64)]
    public string $name;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'quizzes')]
    public Season $season;

    /** @var Collection<int, Question> */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quiz', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordering' => 'ASC'])]
    public private(set) Collection $questions;

    /** @var Collection<int, QuizCandidate> */
    #[ORM\OneToMany(targetEntity: QuizCandidate::class, mappedBy: 'quiz', orphanRemoval: true)]
    public private(set) Collection $candidateData;

    #[ORM\Column(nullable: false, options: ['default' => 1])]
    public int $dropouts = 1;

    /** @var Collection<int, Elimination> */
    #[ORM\OneToMany(targetEntity: Elimination::class, mappedBy: 'quiz', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['created' => 'DESC'])]
    public private(set) Collection $eliminations;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->candidateData = new ArrayCollection();
        $this->eliminations = new ArrayCollection();
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->quiz = $this;
        }

        return $this;
    }

    public function addElimination(Elimination $elimination): self
    {
        $this->eliminations->add($elimination);

        return $this;
    }
}
