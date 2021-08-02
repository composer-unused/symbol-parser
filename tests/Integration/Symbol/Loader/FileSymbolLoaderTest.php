<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration\Symbol\Loader;

use ComposerUnused\SymbolParser\File\FileContentProvider;
use ComposerUnused\SymbolParser\Parser\PHP\ConsumedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\ClassConstStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\NewStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\StaticStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\UsedExtensionSymbolStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\UseStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use ComposerUnused\SymbolParser\Symbol\Loader\FileSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\Provider\FileSymbolProvider;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;
use ComposerUnused\SymbolParser\Test\Integration\AbstractIntegrationTestCase;
use PhpParser\ParserFactory;
use Psr\Log\NullLogger;

use function iterator_to_array;

class FileSymbolLoaderTest extends AbstractIntegrationTestCase
{
    private const ONLY_FILE_DEPS = __DIR__ . '/../../../assets/TestProjects/OnlyFileDependencies';
    private const AUTOLOAD_FILES_REQUIRE = __DIR__ . '/../../../assets/TestProjects/AutoloadFilesWithRequire';

    /**
     * @test
     */
    public function itFindsDefinedFileSymbols(): void
    {
        $package = $this->loadPackage(self::ONLY_FILE_DEPS, 'test/file-dependency');
        $fileLoader = $this->createDefinedFileSymbolLoader(self::ONLY_FILE_DEPS);

        /** @var array<SymbolInterface> $symbols */
        $symbols = iterator_to_array($fileLoader->load($package));

        self::assertCount(1, $symbols);
        self::assertEquals('testfunction', $symbols['testfunction']->getIdentifier());
    }

    /**
     * @test
     */
    public function itFindsConsumedFileSymbols(): void
    {
        $rootPackage = $this->getComposer(self::AUTOLOAD_FILES_REQUIRE)->getPackage();
        $fileLoader = $this->createConsumedFileSymbolLoader(self::AUTOLOAD_FILES_REQUIRE);

        $symbols = iterator_to_array($fileLoader->load($rootPackage));

        self::assertCount(2, $symbols);
        self::assertEquals('Ds\Vector', $symbols['Ds\Vector']->getIdentifier());
        self::assertEquals('json_encode', $symbols['json_encode']->getIdentifier());
    }

    protected function createConsumedFileSymbolLoader(string $baseDir): FileSymbolLoader
    {
        return new FileSymbolLoader(
            $baseDir,
            new FileSymbolProvider(
                new SymbolNameParser(
                    (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
                    new ConsumedSymbolCollector(
                        [
                            new NewStrategy(),
                            new StaticStrategy(),
                            new UseStrategy(),
                            new ClassConstStrategy(),
                            new UsedExtensionSymbolStrategy(
                                get_loaded_extensions(),
                                new NullLogger()
                            )
                        ]
                    )
                ),
                new FileContentProvider()
            ),
            ['files']
        );
    }

    protected function createDefinedFileSymbolLoader(string $baseDir): FileSymbolLoader
    {
        return new FileSymbolLoader(
            $baseDir,
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
