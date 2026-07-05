<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractBaseAnswer implements \Stringable
{
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    public int $ordering = 0;

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
