<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Symbol\Loader;

use Composer\Package\PackageInterface;
use Generator;

final class CompositeSymbolLoader implements SymbolLoaderInterface
{
    /** @var array<SymbolLoaderInterface> */
    private $symbolLoader;

    /**
     * @param array<SymbolLoaderInterface> $symbolLoader
     */
    public function __construct(array $symbolLoader)
    {
        $this->symbolLoader = $symbolLoader;
    }

    public function load(PackageInterface $package): Generator
    {
        foreach ($this->symbolLoader as $loader) {
            yield from $loader->load($package);
        }
    }
}
