<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class BankAnswer implements \Stringable
{
    #[Map(if: false)]
    #[ORM\Column(type: UuidType::NAME)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    public private(set) Uuid $id;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    public int $ordering = 0;

    #[Map(if: false)]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(inversedBy: 'answers')]
    public BankQuestion $bankQuestion;

    public function __construct(
        #[ORM\Column(length: 255)]
        public string $text,
        #[ORM\Column]
        public bool $isRightAnswer = false,
    ) {}

    public function __toString(): string
    {
        return $this->text;
    }
}
