<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->notPath('assets/TestFiles/UseSingleLineNoGroup.php')
            ->in([__DIR__.'/src/', __DIR__.'/tests/'])
    )
;
