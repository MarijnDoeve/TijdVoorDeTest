<?php

declare(strict_types=1);

namespace Tvdt\Enum;

use Symfony\Component\Translation\TranslatableMessage;

enum ScreenColour: string
{
    case Green = 'green';
    case Red = 'red';

    public function label(): TranslatableMessage
    {
        return match ($this) {
            self::Green => new TranslatableMessage('Green'),
            self::Red => new TranslatableMessage('Red'),
        };
    }
}
