<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\GivenAnswerRepository;

#[ORM\Entity(repositoryClass: GivenAnswerRepository::class)]
#[ORM\HasLifecycleCallbacks]
final class GivenAnswer
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public private(set) \DateTimeImmutable $created;

    public function __construct(
        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        private(set) Candidate $candidate,

        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        #[ORM\ManyToOne]
        private(set) Quiz $quiz,

        #[ORM\JoinColumn(nullable: false)]
        #[ORM\ManyToOne(inversedBy: 'givenAnswers')]
        private(set) Answer $answer,
    ) {}

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created = new DateTimeImmutable();
    }
}
