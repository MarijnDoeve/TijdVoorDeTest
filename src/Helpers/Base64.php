<?php

declare(strict_types=1);

namespace App\Helpers;

use Safe\Exceptions\UrlException;

class Base64
{
    private function __construct()
    {
    }

    public static function base64_url_encode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /** @throws UrlException */
    public static function base64_url_decode(string $input): string
    {
        return \Safe\base64_decode(strtr($input, '-_', '+/'), true);
    }
}
