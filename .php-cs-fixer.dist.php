<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude('var')
    ->in([
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1'                  => true,
        '@PSR12'                 => true,
        '@PHP74Migration'        => true,
        'array_syntax'           => ['syntax' => 'short'],
        'no_unused_imports'      => true,
        'ordered_imports'        => true,
        'phpdoc_align'           => ['align' => 'vertical'],
        'phpdoc_indent'          => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package'      => true,
        'phpdoc_order'           => true,
        'phpdoc_separation'      => true,
        'phpdoc_scalar'          => true,
        'phpdoc_trim'            => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
;
