<?php

declare(strict_types=1);

namespace Tvdt\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Safe\Exceptions\UrlException;
use Tvdt\Helpers\Base64;

final class Base64Test extends TestCase
{
    public function testBase64UrlEncode(): void
    {
        $this->assertSame('TWFyaWpu', Base64::base64UrlEncode('Marijn'));
        $this->assertSame('UGhpbGluZQ', Base64::base64UrlEncode('Philine'));

        $this->assertSame('_g', Base64::base64UrlEncode(\chr(254)));
        $this->assertSame('-g', Base64::base64UrlEncode(\chr(250)));
    }

    public function testBase64UrlDecode(): void
    {
        $this->assertSame('Marijn', Base64::base64UrlDecode('TWFyaWpu'));
        $this->assertSame('Philine', Base64::base64UrlDecode('UGhpbGluZQ'));

        $this->assertSame(\chr(254), Base64::base64UrlDecode('_g'));
        $this->assertSame(\chr(250), Base64::base64UrlDecode('-g'));
    }

    public function testBase64UrlDecodeCanHandlePadding(): void
    {
        $this->assertSame('Philine', Base64::base64UrlDecode('UGhpbGluZQ=='));
    }

    public function testBase64UrlDecodeThrowsExceptionOnInvalidInput(): void
    {
        $this->expectException(UrlException::class);
        Base64::base64UrlDecode('Philine==');
    }
}
