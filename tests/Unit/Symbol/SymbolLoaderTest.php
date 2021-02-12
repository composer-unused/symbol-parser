<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Unit\Symbol;

use Composer\Package\Package;
use ComposerUnused\SymbolParser\Symbol\Loader\CompositeSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\Loader\PsrSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\NamespaceSymbol;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;
use Generator;
use PHPUnit\Framework\TestCase;

class SymbolLoaderTest extends TestCase
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
    public function itShouldMatchPsr0(): void
    {
        $package = new Package('test', '*', '*');
        $package->setTargetDir('/');
        $package->setAutoload([
            'psr-0' => [
                'Test\\Namespace\\' => 'src'
            ]
        ]);

        $symbolLoader = new PsrSymbolLoader();
        $symbols = $symbolLoader->load($package);

        $symbolsArray = iterator_to_array($symbols);
        self::assertCount(1, $symbolsArray);
        $this->assertContainsSymbol(new NamespaceSymbol('Test\\Namespace\\'), $symbolsArray);
    }

    /**
     * @test
     */
    public function itShouldMatchPsr4(): void
    {
        $package = new Package('test', '*', '*');
        $package->setTargetDir('/');
        $package->setAutoload([
            'psr-4' => [
                'Test\\Namespace\\' => 'src'
            ]
        ]);

        $symbolLoader = new PsrSymbolLoader();
        $symbols = $symbolLoader->load($package);

        $symbolsArray = iterator_to_array($symbols);
        self::assertCount(1, $symbolsArray);
        $this->assertContainsSymbol(new NamespaceSymbol('Test\\Namespace\\'), $symbolsArray);
    }
}
