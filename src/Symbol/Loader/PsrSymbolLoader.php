<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Symbol\Loader;

use ComposerUnused\Contracts\PackageInterface;
use Generator;
use ComposerUnused\SymbolParser\Symbol\NamespaceSymbol;

use function array_merge;

final class PsrSymbolLoader implements SymbolLoaderInterface
{
    public function load(PackageInterface $package): Generator
    {
        $namespaces = array_merge(
            $package->getAutoload()['psr-4'] ?? [],
            $package->getAutoload()['psr-0'] ?? []
        );

        foreach ($namespaces as $namespace => $dir) {
            yield new NamespaceSymbol($namespace);
        }
    }

    public function withBaseDir(?string $baseDir): SymbolLoaderInterface
    {
        return $this;
    }
}
