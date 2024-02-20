<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->notPath('assets/TestFiles/UseSingleLineNoGroup.php')
            ->in([__DIR__.'/src/', __DIR__.'/tests/'])
    )
;
