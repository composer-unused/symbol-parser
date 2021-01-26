<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration\Symbol\Loader;

use ComposerUnused\SymbolParser\File\FileContentProvider;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use ComposerUnused\SymbolParser\Symbol\Loader\FileSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\Provider\FileSymbolProvider;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;
use ComposerUnused\SymbolParser\Test\Integration\AbstractIntegrationTestCase;
use PhpParser\ParserFactory;

use function iterator_to_array;

class FileSymbolLoaderTest extends AbstractIntegrationTestCase
{
    private const BASE_DIR = __DIR__ . '/../../../assets/TestProjects/OnlyFileDependencies';

    /**
     * @test
     */
    public function itFindsDefinedFileSymbols(): void
    {
        $package = $this->loadPackage(self::BASE_DIR, 'test/file-dependency');
        $fileLoader = $this->createFileSymbolLoader();

        /** @var array<SymbolInterface> $symbols */
        $symbols = iterator_to_array($fileLoader->load($package));

        self::assertCount(1, $symbols);
        self::assertEquals('testfunction', $symbols['testfunction']->getIdentifier());
    }

    private function createFileSymbolLoader(): FileSymbolLoader
    {
        return new FileSymbolLoader(
            self::BASE_DIR,
            new FileSymbolProvider(
                new SymbolNameParser(
                    (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
                    new DefinedSymbolCollector()
                ),
                new FileContentProvider()
            ),
            ['files']
        );
    }
}
