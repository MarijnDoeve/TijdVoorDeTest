<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\EliminationRepository;

#[ORM\Entity(repositoryClass: EliminationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Elimination
{
    public const string SCREEN_GREEN = 'green';

    public const string SCREEN_RED = 'red';

    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSONB)]
    public array $data = [];

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    public private(set) \DateTimeImmutable $created;

    public function __construct(
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        #[ORM\ManyToOne(inversedBy: 'eliminations')]
        public Quiz $quiz,
    ) {}

    /** @param InputBag<bool|float|int|string> $inputBag */
    public function updateFromInputBag(InputBag $inputBag): self
    {
        foreach (array_keys($this->data) as $name) {
            $newColour = $inputBag->get('colour-'.mb_strtolower($name));
            if (\is_string($newColour)) {
                $this->data[$name] = $inputBag->get('colour-'.mb_strtolower($name));
            }
        }

        return $this;
    }

    public function getScreenColour(?string $name): ?string
    {
        return $this->data[$name] ?? null;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created = new DateTimeImmutable();
    }
}
