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
class Quiz
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

    /**
     * Get errors for all questions in the quiz.
     * Returns an array where keys are question IDs and values are error messages.
     *
     * @return array<string, string>
     */
    public function getQuestionErrors(): array
    {
        $errors = [];

        // Check if any answer in the entire quiz has candidate relations
        $hasCandidateRelations = false;
        foreach ($this->questions as $question) {
            foreach ($question->answers as $answer) {
                if ($answer->candidates->count() > 0) {
                    $hasCandidateRelations = true;
                    break 2;
                }
            }
        }

        foreach ($this->questions as $question) {
            $error = $this->getQuestionError($question, $hasCandidateRelations);
            if (null !== $error) {
                $errors[$question->id->toString()] = $error;
            }
        }

        return $errors;
    }

    private function getQuestionError(Question $question, bool $hasCandidateRelations): ?string
    {
        if (0 === \count($question->answers)) {
            return 'This question has no answers';
        }

        $correctAnswers = $question->answers->filter(static fn (Answer $answer): bool => $answer->isRightAnswer)->count();

        if (0 === $correctAnswers) {
            return 'This question has no correct answers';
        }

        if ($correctAnswers > 1) {
            return 'This question has multiple correct answers';
        }

        // Only validate candidate-answer relations if at least one exists in the quiz
        if ($hasCandidateRelations) {
            // Get only active candidates for this quiz
            $activeCandidates = [];
            foreach ($this->candidateData as $quizCandidate) {
                if ($quizCandidate->active) {
                    $activeCandidates[] = $quizCandidate->candidate;
                }
            }

            $candidateCounts = [];

            // Count how many times each candidate appears in answers
            foreach ($question->answers as $answer) {
                foreach ($answer->candidates as $candidate) {
                    $candidateId = $candidate->id->toString();
                    if (!isset($candidateCounts[$candidateId])) {
                        $candidateCounts[$candidateId] = ['name' => $candidate->name, 'count' => 0];
                    }

                    ++$candidateCounts[$candidateId]['count'];
                }
            }

            // Check for missing and duplicate candidates (only active ones)
            $missing = [];
            $duplicates = [];

            foreach ($activeCandidates as $candidate) {
                $candidateId = $candidate->id->toString();
                $count = $candidateCounts[$candidateId]['count'] ?? 0;

                if (0 === $count) {
                    $missing[] = $candidate->name;
                } elseif ($count > 1) {
                    $duplicates[] = $candidate->name;
                }
            }

            if ($missing !== [] || $duplicates !== []) {
                $errors = [];
                if ($missing !== []) {
                    // If all active candidates are missing, show a special message
                    if (\count($missing) === \count($activeCandidates)) {
                        $errors[] = 'No candidates assigned to this question';
                    } else {
                        $errors[] = 'Missing candidates: '.implode(', ', $missing);
                    }
                }

                if ($duplicates !== []) {
                    $errors[] = 'Duplicate candidates: '.implode(', ', $duplicates);
                }

                return implode('. ', $errors);
            }
        }

        return null;
    }
}
