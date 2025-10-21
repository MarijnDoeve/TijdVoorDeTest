<?php

declare(strict_types=1);

namespace Tvdt\Dto;

use Symfony\Component\Uid\Uuid;

final readonly class Result
{
    public function __construct(
        public Uuid $id,
        public string $name,
        public int $correct,
        public float $corrections,
        public \DateInterval $time,
        public float $score,
    ) {}
}
