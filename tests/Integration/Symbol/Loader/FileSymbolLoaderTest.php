<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration\Symbol\Loader;

use Composer\Package\PackageInterface;
use ComposerUnused\SymbolParser\File\FileContentProvider;
use ComposerUnused\SymbolParser\Parser\PHP\AutoloadType;
use ComposerUnused\SymbolParser\Parser\PHP\ConsumedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\ClassConstStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\FunctionInvocationStrategy;
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
    public function itFindsForeignDefinedFileSymbols(): void
    {
        $symbols = $this->loadDefinedFileSymbols(self::ONLY_FILE_DEPS, 'test/file-dependency', [AutoloadType::FILES]);

        self::assertCount(2, $symbols);
        self::assertArrayHasKey('testfunction', $symbols);
        self::assertArrayHasKey('testfunction2', $symbols);
    }

    /**
     * @test
     */
    public function itFindsConsumedFileSymbols(): void
    {
        $symbols = $this->loadConsumedFileSymbols(self::ONLY_FILE_DEPS);

        self::assertCount(2, $symbols);
        self::assertArrayHasKey('testfunction', $symbols);
        self::assertArrayHasKey('testfunction2', $symbols);
    }

    /**
     * @test
     */
    public function itFindsConsumedAutoloadFileRequireSymbols(): void
    {
        $symbols = $this->loadConsumedFileSymbols(self::AUTOLOAD_FILES_REQUIRE, [AutoloadType::FILES]);

        self::assertCount(2, $symbols);
        self::assertArrayHasKey('Ds\Vector', $symbols);
        self::assertArrayHasKey('json_encode', $symbols);
    }
}
