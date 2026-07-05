<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Enum\LabelColour;
use Tvdt\Repository\QuestionLabelRepository;

#[ORM\Entity(repositoryClass: QuestionLabelRepository::class)]
#[ORM\UniqueConstraint(fields: ['name', 'season'])]
#[ORM\UniqueConstraint(name: 'uq_question_label_slug_season', fields: ['slug', 'season'])]
class QuestionLabel implements \Stringable
{
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'questionLabels')]
    public Season $season;

    /** @var Collection<int, BankQuestion> */
    #[ORM\ManyToMany(targetEntity: BankQuestion::class, mappedBy: 'labels')]
    public private(set) Collection $bankQuestions;

    #[ORM\Column(length: 16, enumType: LabelColour::class, options: ['default' => 'secondary'])]
    public LabelColour $colour = LabelColour::Slate;

    #[ORM\Column(length: 64)]
    public string $slug = '';

    public function __construct(
        #[ORM\Column(length: 64)]
        public string $name,
    ) {
        $this->bankQuestions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
