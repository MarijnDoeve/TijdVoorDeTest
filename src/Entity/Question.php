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
class Question implements \Stringable
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

    public function __toString(): string
    {
        return $this->question;
    }
}
