<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $question;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private Quiz $quiz;

    #[ORM\Column]
    private bool $enabled = true;

    /** @var Collection<int, Answer> */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ['persist'], orphanRemoval: true)]
    private Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

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

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    /** @return Collection<int, Answer> */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function getErrors(): ?string
    {
        if (0 === \count($this->answers)) {
            return 'This question has no answers';
        }

        $correctAnswers = $this->answers->filter(static fn (Answer $answer): ?bool => $answer->isRightAnswer())->count();

        if (0 === $correctAnswers) {
            return 'This question has no correct answers';
        }

        if ($correctAnswers > 1) {
            return 'This question has multiple correct answers';
        }

        return null;
    }
}
