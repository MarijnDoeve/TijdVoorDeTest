<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Uid\Uuid;
use Tvdt\Repository\EliminationRepository;

#[Gedmo\SoftDeleteable]
#[ORM\Entity(repositoryClass: EliminationRepository::class)]
class Elimination
{
    use SoftDeleteableEntity;
    use TimestampableEntity;

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
        if (null === $name) {
            return null;
        }

        return $this->data[$name] ?? null;
    }
}
