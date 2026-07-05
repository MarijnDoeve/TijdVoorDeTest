<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Tvdt\Repository\BankQuestionRepository;

#[Gedmo\Loggable(logEntryClass: LogEntry::class)]
#[ORM\Entity(repositoryClass: BankQuestionRepository::class)]
class BankQuestion implements \Stringable
{
    #[Map(if: false)]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[Gedmo\Versioned]
    #[ORM\Column(length: 255)]
    public string $question;

    #[Map(if: false)]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'bankQuestions')]
    public Season $season;

    #[Gedmo\Versioned]
    #[Map(if: false)]
    #[ORM\Column(options: ['default' => false])]
    public bool $reusable = false;

    /** @var Collection<int, QuestionLabel> */
    #[Map(if: false)]
    #[ORM\ManyToMany(targetEntity: QuestionLabel::class, inversedBy: 'bankQuestions')]
    public private(set) Collection $labels;

    /** @var Collection<int, BankAnswer> */
    #[Map(if: false)]
    #[ORM\OneToMany(targetEntity: BankAnswer::class, mappedBy: 'bankQuestion', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordering' => 'ASC'])]
    public private(set) Collection $answers;

    /** @var Collection<int, BankQuestionUsage> */
    #[Map(if: false)]
    #[ORM\OneToMany(targetEntity: BankQuestionUsage::class, mappedBy: 'bankQuestion', cascade: ['persist'], orphanRemoval: true)]
    public private(set) Collection $usages;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->usages = new ArrayCollection();
    }

    public function addAnswer(BankAnswer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->bankQuestion = $this;
        }

        return $this;
    }

    public function removeAnswer(BankAnswer $answer): static
    {
        $this->answers->removeElement($answer);

        return $this;
    }

    public function addLabel(QuestionLabel $label): static
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    public function removeLabel(QuestionLabel $label): static
    {
        $this->labels->removeElement($label);

        return $this;
    }

    public function addUsage(BankQuestionUsage $usage): static
    {
        if (!$this->usages->contains($usage)) {
            $this->usages->add($usage);
        }

        return $this;
    }

    public bool $isUsed {
        get => !$this->usages->isEmpty();
    }

    public bool $canBeAssigned {
        get => $this->reusable || !$this->isUsed;
    }

    /** True when the question is fully complete and can be assigned to a quiz. */
    public bool $isCompleteForQuiz {
        get => $this->answers->count() >= 2
            && 1 === $this->answers->filter(static fn (BankAnswer $answer): bool => $answer->isRightAnswer)->count();
    }

    public function isUsedInQuiz(Quiz $quiz): bool
    {
        return $this->usages->exists(static fn (int $key, BankQuestionUsage $usage): bool => $usage->quiz === $quiz);
    }

    #[Assert\Callback]
    public function validateAnswers(ExecutionContextInterface $context): void
    {
        if ($this->answers->isEmpty()) {
            return;
        }

        $this->answers->filter(static fn (BankAnswer $answer): bool => $answer->isRightAnswer)->count();

        if ($this->answers->count() < 2) {
            $context->buildViolation('A question needs at least two answers')
                ->atPath('answers')
                ->addViolation();
        }
    }

    public function __toString(): string
    {
        return $this->question;
    }
}
