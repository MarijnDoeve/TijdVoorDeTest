<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Helpers\Base64;
use Tvdt\Repository\CandidateRepository;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
#[ORM\UniqueConstraint(fields: ['name', 'season'])]
final class Candidate
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'candidates')]
    public Season $season;

    /** @var Collection<int, Answer> */
    #[ORM\ManyToMany(targetEntity: Answer::class, mappedBy: 'candidates')]
    public private(set) Collection $answersOnCandidate;

    /** @var Collection<int, GivenAnswer> */
    #[ORM\OneToMany(targetEntity: GivenAnswer::class, mappedBy: 'candidate', orphanRemoval: true)]
    public private(set) Collection $givenAnswers;

    /** @var Collection<int, QuizCandidate> */
    #[ORM\OneToMany(targetEntity: QuizCandidate::class, mappedBy: 'candidate', orphanRemoval: true)]
    public private(set) Collection $quizData;

    public string $nameHash {
        get => Base64::base64UrlEncode($this->name);
    }

    public function __construct(
        #[ORM\Column(length: 16)]
        public string $name,
    ) {
        $this->answersOnCandidate = new ArrayCollection();
        $this->givenAnswers = new ArrayCollection();
        $this->quizData = new ArrayCollection();
    }

    public function addAnswersOnCandidate(Answer $answersOnCandidate): void
    {
        if (!$this->answersOnCandidate->contains($answersOnCandidate)) {
            $this->answersOnCandidate->add($answersOnCandidate);
            $answersOnCandidate->addCandidate($this);
        }
    }

    public function removeAnswersOnCandidate(Answer $answersOnCandidate): void
    {
        if ($this->answersOnCandidate->removeElement($answersOnCandidate)) {
            $answersOnCandidate->removeCandidate($this);
        }
    }
}
