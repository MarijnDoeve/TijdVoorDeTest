<?php

declare(strict_types=1);

namespace Tvdt\Helpers;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Stores any string that would otherwise be auto-detected as a spreadsheet formula (or that
 * spreadsheet software re-interprets as one on open/paste) as plain text instead, to prevent
 * formula injection via user-controlled export data (e.g. a candidate or answer named
 * `=WEBSERVICE(...)`).
 */
final class FormulaInjectionSafeValueBinder extends DefaultValueBinder
{
    private const string DANGEROUS_PREFIXES = "=+-@\t\r";

    #[\Override]
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (\is_string($value) && '' !== $value && str_contains(self::DANGEROUS_PREFIXES, $value[0])) {
            $cell->setValueExplicit($value);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
