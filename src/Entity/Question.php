<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\QuestionRepository;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    public int $ordering;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $question;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'questions')]
    public Quiz $quiz;

    #[ORM\Column]
    public bool $enabled = true;

    /** @var Collection<int, Answer> */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordering' => 'ASC'])]
    public private(set) Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->question = $this;
        }

        return $this;
    }

    public function getErrors(): ?string
    {
        if (0 === \count($this->answers)) {
            return 'This question has no answers';
        }

        $correctAnswers = $this->answers->filter(static fn (Answer $answer): bool => $answer->isRightAnswer)->count();

        if (0 === $correctAnswers) {
            return 'This question has no correct answers';
        }

        if ($correctAnswers > 1) {
            return 'This question has multiple correct answers';
        }

        // Check if any answer in the entire quiz has candidate relations
        $hasCandidateRelations = false;
        foreach ($this->quiz->questions as $quizQuestion) {
            foreach ($quizQuestion->answers as $answer) {
                if ($answer->candidates->count() > 0) {
                    $hasCandidateRelations = true;
                    break 2;
                }
            }
        }

        // Only validate candidate-answer relations if at least one exists in the quiz
        if ($hasCandidateRelations) {
            $seasonCandidates = $this->quiz->season->candidates;
            $candidateCounts = [];

            // Count how many times each candidate appears in answers
            foreach ($this->answers as $answer) {
                foreach ($answer->candidates as $candidate) {
                    $candidateId = $candidate->id->toString();
                    if (!isset($candidateCounts[$candidateId])) {
                        $candidateCounts[$candidateId] = ['name' => $candidate->name, 'count' => 0];
                    }
                    ++$candidateCounts[$candidateId]['count'];
                }
            }

            // Check for missing and duplicate candidates
            $missing = [];
            $duplicates = [];

            foreach ($seasonCandidates as $candidate) {
                $candidateId = $candidate->id->toString();
                $count = $candidateCounts[$candidateId]['count'] ?? 0;

                if (0 === $count) {
                    $missing[] = $candidate->name;
                } elseif ($count > 1) {
                    $duplicates[] = $candidate->name;
                }
            }

            if (!empty($missing) || !empty($duplicates)) {
                $errors = [];
                if (!empty($missing)) {
                    // If all candidates are missing, show a special message
                    if (\count($missing) === $seasonCandidates->count()) {
                        $errors[] = 'No candidates assigned to this question';
                    } else {
                        $errors[] = 'Missing candidates: '.implode(', ', $missing);
                    }
                }
                if (!empty($duplicates)) {
                    $errors[] = 'Duplicate candidates: '.implode(', ', $duplicates);
                }

                return implode('. ', $errors);
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return $this->question;
    }
}
