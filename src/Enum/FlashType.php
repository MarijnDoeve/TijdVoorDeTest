<?php

declare(strict_types=1);

namespace App\Enum;

enum FlashType: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';
    case Ligt = 'light';
    case Dark = 'dark';
}
