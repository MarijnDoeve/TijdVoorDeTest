<?php

declare(strict_types=1);

namespace Tvdt\Enum;

use Symfony\Component\Translation\TranslatableMessage;

enum LabelColour: string
{
    case Blue = 'primary';
    case Gray = 'secondary';
    case Green = 'success';
    case Red = 'danger';
    case Yellow = 'warning';
    case Cyan = 'info';
    case White = 'light';

    public function label(): TranslatableMessage
    {
        return match ($this) {
            self::Blue => new TranslatableMessage('Blue'),
            self::Gray => new TranslatableMessage('Gray'),
            self::Green => new TranslatableMessage('Green'),
            self::Red => new TranslatableMessage('Red'),
            self::Yellow => new TranslatableMessage('Yellow'),
            self::Cyan => new TranslatableMessage('Cyan'),
            self::White => new TranslatableMessage('White'),
        };
    }
}
