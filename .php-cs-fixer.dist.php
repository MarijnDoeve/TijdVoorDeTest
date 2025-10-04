<?php

declare(strict_types=1);
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = new Finder()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('bin')
;

return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'fully_qualified_strict_types' => ['import_symbols' => true],
        'linebreak_after_opening_tag' => true,
        'mb_str_functions' => true,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'php_unit_strict' => true,
        'phpdoc_line_span' => ['const' => 'single', 'method' => 'single', 'property' => 'single'],
        'phpdoc_order' => true,
        'single_line_empty_body' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'ordered_attributes' => true,
        'heredoc_indentation' => ['indentation' => 'start_plus_one'],
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters']],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
