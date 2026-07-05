<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\AnswerRepository;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer extends AbstractBaseAnswer
{
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'answers')]
    public Question $question;

    /** @var Collection<int, Candidate> */
    #[ORM\ManyToMany(targetEntity: Candidate::class, inversedBy: 'answersOnCandidate')]
    public private(set) Collection $candidates;

    /** @var Collection<int, GivenAnswer> */
    #[ORM\OneToMany(targetEntity: GivenAnswer::class, mappedBy: 'answer', orphanRemoval: true)]
    public private(set) Collection $givenAnswers;

    public function __construct(string $text, bool $isRightAnswer = false)
    {
        parent::__construct($text, $isRightAnswer);
        $this->candidates = new ArrayCollection();
        $this->givenAnswers = new ArrayCollection();
    }

    public function addCandidate(Candidate $candidate): void
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates->add($candidate);
        }
    }

    public function removeCandidate(Candidate $candidate): void
    {
        $this->candidates->removeElement($candidate);
    }
}
