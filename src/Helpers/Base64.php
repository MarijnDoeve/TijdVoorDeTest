<?php

declare(strict_types=1);

namespace Tvdt\Helpers;

use Safe\Exceptions\UrlException;

class Base64
{
    public static function base64UrlEncode(string $input): string
    {
        return mb_rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    /** @throws UrlException */
    public static function base64UrlDecode(string $input): string
    {
        return \Safe\base64_decode(strtr($input, '-_', '+/'), true);
    }
}
