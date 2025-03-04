<?php

declare(strict_types=1);

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    $appEnv = !empty($context['APP_ENV']) ? (string) $context['APP_ENV'] : 'prod';
    $appDebug = !empty($context['APP_DEBUG']) ? filter_var($context['APP_DEBUG'], \FILTER_VALIDATE_BOOL) : 'prod' !== $appEnv;

    return new Kernel($appEnv, $appDebug);
};
