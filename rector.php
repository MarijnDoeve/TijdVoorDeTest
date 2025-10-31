<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Bridge\Symfony\Routing\SymfonyRoutesProvider;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSymfonyContainerXml(__DIR__.'/var/cache/dev/Tvdt_KernelDevDebugContainer.xml')
    ->withSymfonyContainerPhp(__DIR__.'/tests/symfony-container.php')
    ->registerService(SymfonyRoutesProvider::class, SymfonyRoutesProviderInterface::class)
    ->withParallel()
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
    )
    ->withAttributesSets(all: true)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
;
