<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\AnswerRepository;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
final class Answer
{
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    public int $ordering = 0;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'answers')]
    public Question $question;

    /** @var Collection<int, Candidate> */
    #[ORM\ManyToMany(targetEntity: Candidate::class, inversedBy: 'answersOnCandidate')]
    public private(set) Collection $candidates;

    /** @var Collection<int, GivenAnswer> */
    #[ORM\OneToMany(targetEntity: GivenAnswer::class, mappedBy: 'answer', orphanRemoval: true)]
    public private(set) Collection $givenAnswers;

    public function __construct(
        #[ORM\Column(length: 255)]
        public string $text,
        #[ORM\Column]
        public bool $isRightAnswer = false,
    ) {
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
