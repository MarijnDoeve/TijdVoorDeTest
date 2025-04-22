<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use Sentry\SentryBundle\SentryBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\UX\TwigComponent\TwigComponentBundle;
use SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

return [
    DoctrineBundle::class => ['all' => true],
    DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    EasyAdminBundle::class => ['all' => true],
    FrameworkBundle::class => ['all' => true],
    MakerBundle::class => ['dev' => true],
    SecurityBundle::class => ['all' => true],
    SentryBundle::class => ['prod' => true],
    SymfonyCastsVerifyEmailBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    TwigComponentBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
];
