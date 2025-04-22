<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CandidateRepository;

/**
 * @phpstan-import-type ResultList from CandidateRepository
 */
class EliminationService
{
    /** @phpstan-param ResultList $result */
    public function createEliminationFromResult(array $result): void {}
}
