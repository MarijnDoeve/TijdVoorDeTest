<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EliminationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Safe\DateTimeImmutable;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EliminationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Elimination
{
    public const string SCREEN_GREEN = 'green';
    public const string SCREEN_RED = 'red';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $created;

    public function __construct(
        #[ORM\ManyToOne(inversedBy: 'eliminations')]
        #[ORM\JoinColumn(nullable: false)]
        private Quiz $quiz,
    ) {}

    public function getId(): Uuid
    {
        return $this->id;
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        return $this->data;
    }

    /** @param array<string, mixed> $data */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    /** @param InputBag<bool|float|int|string> $inputBag */
    public function updateFromInputBag(InputBag $inputBag): self
    {
        foreach ($this->data as $name => $screenColour) {
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

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }
}
