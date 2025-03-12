<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CandidateRepository;

/**
 * @phpstan-import-type ResultArray from CandidateRepository
 */
class EliminationService
{
    /** @phpstan-param ResultArray $result */
    public function createEliminationFromResult(array $result): void {}
}
