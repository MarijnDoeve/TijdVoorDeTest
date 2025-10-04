<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Helpers\Base64;
use Tvdt\Repository\CandidateRepository;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
#[ORM\UniqueConstraint(fields: ['name', 'season'])]
class Candidate
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    private Uuid $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'candidates')]
    private Season $season;

    /** @var Collection<int, Answer> */
    #[ORM\ManyToMany(targetEntity: Answer::class, mappedBy: 'candidates')]
    private Collection $answersOnCandidate;

    /** @var Collection<int, GivenAnswer> */
    #[ORM\OneToMany(targetEntity: GivenAnswer::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $givenAnswers;

    /** @var Collection<int, QuizCandidate> */
    #[ORM\OneToMany(targetEntity: QuizCandidate::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $quizData;

    public function __construct(
        #[ORM\Column(length: 16)]
        private string $name,
    ) {
        $this->answersOnCandidate = new ArrayCollection();
        $this->givenAnswers = new ArrayCollection();
        $this->quizData = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /** @return Collection<int, Answer> */
    public function getAnswersOnCandidate(): Collection
    {
        return $this->answersOnCandidate;
    }

    public function addAnswersOnCandidate(Answer $answersOnCandidate): static
    {
        if (!$this->answersOnCandidate->contains($answersOnCandidate)) {
            $this->answersOnCandidate->add($answersOnCandidate);
            $answersOnCandidate->addCandidate($this);
        }

        return $this;
    }

    public function removeAnswersOnCandidate(Answer $answersOnCandidate): static
    {
        if ($this->answersOnCandidate->removeElement($answersOnCandidate)) {
            $answersOnCandidate->removeCandidate($this);
        }

        return $this;
    }

    /** @return Collection<int, GivenAnswer> */
    public function getGivenAnswers(): Collection
    {
        return $this->givenAnswers;
    }

    /** @return Collection<int, QuizCandidate> */
    public function getQuizData(): Collection
    {
        return $this->quizData;
    }

    public function getNameHash(): string
    {
        return Base64::base64UrlEncode($this->name);
    }
}
