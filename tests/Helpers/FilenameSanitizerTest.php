<?php

declare(strict_types=1);

namespace Tvdt\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Tvdt\Helpers\FilenameSanitizer;

final class FilenameSanitizerTest extends TestCase
{
    public function testReplacesSpacesWithDashes(): void
    {
        $this->assertSame('Krtek-Weekend', FilenameSanitizer::sanitize('Krtek Weekend'));
    }

    public function testStripsPathSeparatorsAndTraversal(): void
    {
        $this->assertSame('etc-passwd', FilenameSanitizer::sanitize('../../etc/passwd'));
        $this->assertSame('a-b', FilenameSanitizer::sanitize('a/b'));
        $this->assertSame('a-b', FilenameSanitizer::sanitize('a\\b'));
    }

    public function testStripsControlCharactersAndSpecialSymbols(): void
    {
        $this->assertSame('Quiz-1-script', FilenameSanitizer::sanitize("Quiz #1 <script>\0"));
    }

    public function testTransliteratesUnicodeToAscii(): void
    {
        $this->assertSame('Weird-Name', FilenameSanitizer::sanitize('Wéird Ñame'));
    }

    public function testTransliteratesAtSignInEmail(): void
    {
        $this->assertSame('test-example-org', FilenameSanitizer::sanitize('test@example.org'));
    }

    public function testReturnsUnnamedForEmptyOrFullyStrippedInput(): void
    {
        $this->assertSame('unnamed', FilenameSanitizer::sanitize(''));
        $this->assertSame('unnamed', FilenameSanitizer::sanitize('///'));
    }
}
