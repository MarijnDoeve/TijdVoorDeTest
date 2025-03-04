<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helpers\Base64;
use App\Repository\CandidateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'candidates')]
    #[ORM\JoinColumn(nullable: false)]
    private Season $season;

    /** @var Collection<int, Answer> */
    #[ORM\ManyToMany(targetEntity: Answer::class, mappedBy: 'candidates')]
    private Collection $answersOnCandidate;

    /** @var Collection<int, GivenAnswer> */
    #[ORM\OneToMany(targetEntity: GivenAnswer::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $givenAnswers;

    /** @var Collection<int, Correction> */
    #[ORM\OneToMany(targetEntity: Correction::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $corrections;

    public function __construct(
        #[ORM\Column(length: 16)]
        private string $name,
    ) {
        $this->answersOnCandidate = new ArrayCollection();
        $this->givenAnswers = new ArrayCollection();
        $this->corrections = new ArrayCollection();
    }

    public function getId(): ?Uuid
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

    public function addGivenAnswer(GivenAnswer $givenAnswer): static
    {
        if (!$this->givenAnswers->contains($givenAnswer)) {
            $this->givenAnswers->add($givenAnswer);
            $givenAnswer->setCandidate($this);
        }

        return $this;
    }

    /** @return Collection<int, Correction> */
    public function getCorrections(): Collection
    {
        return $this->corrections;
    }

    public function addCorrection(Correction $correction): static
    {
        if (!$this->corrections->contains($correction)) {
            $this->corrections->add($correction);
            $correction->setCandidate($this);
        }

        return $this;
    }

    public function getNameHash(): string
    {
        return Base64::base64_url_encode($this->name);
    }
}
