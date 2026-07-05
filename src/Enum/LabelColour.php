<?php

declare(strict_types=1);

namespace Tvdt\Enum;

enum LabelColour: string
{
    case Cobalt = 'primary';
    case Slate = 'secondary';
    case Emerald = 'success';
    case Crimson = 'danger';
    case Amber = 'warning';
    case Sky = 'info';
    case Chalk = 'light';
    case Graphite = 'dark';
}
