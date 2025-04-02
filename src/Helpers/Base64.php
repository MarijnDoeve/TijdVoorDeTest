<?php

declare(strict_types=1);

namespace App\Helpers;

use Safe\Exceptions\UrlException;
use function rtrim;

class Base64
{
    private function __construct()
    {
    }

    /**
     * @param string $name name to hash
     * @return string hashed name
     */
    public static function base64_url_encode(string $name): string
    {
        return rtrim(strtr(base64_encode($name), '+/', '-_'), '=');
    }

    /**
     * @param string $hash hashed name
     * @return string plaintext name
     * @throws UrlException
     */
    public static function base64_url_decode(string $hash): string
    {
        return \Safe\base64_decode(strtr($hash, '-_', '+/'), true);
    }
}
