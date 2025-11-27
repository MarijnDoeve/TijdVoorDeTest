<?php

declare(strict_types=1);

namespace Tvdt\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Safe\Exceptions\UrlException;
use Tvdt\Helpers\Base64;

class Base64Test extends TestCase
{
    public function testBase64UrlEncode()
    {
        $this->assertEquals('TWFyaWpu', Base64::base64UrlEncode('Marijn'));
        $this->assertEquals('UGhpbGluZQ', Base64::base64UrlEncode('Philine'));

        $this->assertEquals('_g', Base64::base64UrlEncode(\chr(254)));
        $this->assertEquals('-g', Base64::base64UrlEncode(\chr(250)));
    }

    public function testBase64UrlDecode()
    {
        $this->assertEquals('Marijn', Base64::base64UrlDecode('TWFyaWpu'));
        $this->assertEquals('Philine', Base64::base64UrlDecode('UGhpbGluZQ'));

        $this->assertEquals(\chr(254), Base64::base64UrlDecode('_g'));
        $this->assertEquals(\chr(250), Base64::base64UrlDecode('-g'));
    }

    public function testBase64UrlDecodeCanHandlePadding()
    {
        $this->assertEquals('Philine', Base64::base64UrlDecode('UGhpbGluZQ=='));
    }

    public function testBase64UrlDecodeThrowsExceptionOnInvalidInput()
    {
        $this->expectException(UrlException::class);
        Base64::base64UrlDecode('Philine==');
    }
}
