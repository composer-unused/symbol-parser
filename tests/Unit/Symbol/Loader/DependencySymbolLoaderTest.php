<?php

declare(strict_types=1);

namespace Icanhazstring\Composer\Test\Unused\Unit\Symbol\Loader;

use Composer\Package\Package;
use Generator;
use ComposerUnused\SymbolParser\Symbol\Loader\CompositeSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\Loader\FileSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\Loader\SymbolLoaderInterface;
use ComposerUnused\SymbolParser\Symbol\Symbol;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;
use PHPUnit\Framework\TestCase;

class DependencySymbolLoaderTest extends TestCase
{
    /**
     * @param array<mixed> $values
     * @return Generator<mixed>
     */
    protected function arrayAsGenerator(array $values): Generator
    {
        yield from $values;
    }

    /**
     * @param array<SymbolInterface> $symbolHaystack
     */
    private function assertContainsSymbol(SymbolInterface $symbol, array $symbolHaystack): void
    {
        foreach ($symbolHaystack as $refSymbol) {
            if ($refSymbol->matches($symbol)) {
                return;
            }
        }

        self::fail($symbol->getIdentifier() . ' not found in haystack');
    }

    /**
     * @test
     */
    public function itShouldReturnEmptySymbolsOnEmptyPackage(): void
    {
        $package = new Package('test', '*', '*');
        $package->setTargetDir('/');
        $package->setAutoload([]);

        $firstSymbolLoader = $this->getMockForAbstractClass(SymbolLoaderInterface::class);
        $firstSymbolLoader->method('load')->willReturn($this->arrayAsGenerator([]));

        $secondSymbolLoader = $this->getMockForAbstractClass(SymbolLoaderInterface::class);
        $secondSymbolLoader->method('load')->willReturn($this->arrayAsGenerator([]));

        $firstSymbolLoader = new CompositeSymbolLoader([$secondSymbolLoader, $firstSymbolLoader]);
        $symbols = $firstSymbolLoader->load($package);

        self::assertEmpty(iterator_to_array($symbols));
    }

    /**
     * @test
     */
    public function itShouldMatchFiles(): void
    {
        $package = new Package('test', '*', '*');
        $package->setTargetDir('/foobar');
        $package->setAutoload([
            'files' => [
                'include/functions.php'
            ]
        ]);

        $firstSymbolLoader = $this->getMockForAbstractClass(SymbolLoaderInterface::class);
        $firstSymbolLoader->method('load')->willReturn($this->arrayAsGenerator([]));

        $secondSymbolLoader = $this->getMockForAbstractClass(SymbolLoaderInterface::class);
        $secondSymbolLoader
            ->expects(self::once())
            ->method('load')
            ->willReturn($this->arrayAsGenerator([
                new Symbol('testfunction')
            ]));

        $firstSymbolLoader = new CompositeSymbolLoader([$secondSymbolLoader, $firstSymbolLoader]);
        $symbols = $firstSymbolLoader->load($package);

        $symbolsArray = iterator_to_array($symbols);
        self::assertCount(1, $symbolsArray);
        $this->assertContainsSymbol(new Symbol('testfunction'), $symbolsArray);
    }
}
