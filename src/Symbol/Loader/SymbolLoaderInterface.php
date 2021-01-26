<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Symbol\Loader;

use Composer\Package\PackageInterface;
use Generator;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;

interface SymbolLoaderInterface
{
    /**
     * @return Generator<SymbolInterface>
     */
    public function load(PackageInterface $package): Generator;
}
