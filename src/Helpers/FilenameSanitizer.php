<?php

declare(strict_types=1);

namespace Tvdt\Helpers;

use Symfony\Component\String\Slugger\AsciiSlugger;

class FilenameSanitizer
{
    /** Slugs user-supplied text (e.g. a season/quiz name) into a string safe to use as a zip entry path segment or a downloaded filename. */
    public static function sanitize(string $value): string
    {
        $slug = new AsciiSlugger()->slug($value)->toString();

        return '' === $slug ? 'unnamed' : $slug;
    }
}
