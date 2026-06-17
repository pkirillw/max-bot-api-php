<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER' => true,
        // Современный PHP: строгие типы, alphabetic imports, no trailing whitespace.
        'declare_strict_types' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'concat_space' => ['spacing' => 'one'],
        // PER требует @covers на каждом тесте — нам это не нужно.
        'php_unit_test_class_requires_covers' => false,
        // Не трогаем phpdoc-теги которые PHPStan/Psalm используют для inference.
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
    ])
    ->setFinder($finder);
