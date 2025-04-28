<?php

declare(strict_types=1);

namespace App\Exception;

class SpreadsheetDataException extends SpreadsheetException
{
    /** @param list<string> $errors */
    public function __construct(
        public readonly array $errors,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
