<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration\Symbol\Loader;

use ComposerUnused\SymbolParser\Parser\PHP\AutoloadType;
use ComposerUnused\SymbolParser\Test\Integration\AbstractIntegrationTestCase;

class FileSymbolLoaderTest extends AbstractIntegrationTestCase
{
    private const ONLY_FILE_DEPS = __DIR__ . '/../../../assets/TestProjects/OnlyFileDependencies';
    private const AUTOLOAD_FILES_REQUIRE = __DIR__ . '/../../../assets/TestProjects/AutoloadFilesWithRequire';
    private const ARRAY_NAMESPACE = __DIR__ . '/../../../assets/TestProjects/ArrayNamespace';

    /**
     * @test
     */
    public function itFindsForeignDefinedFileSymbols(): void
    {
        $symbols = $this->loadDefinedFileSymbols(self::ONLY_FILE_DEPS, [AutoloadType::FILES], 'test/file-dependency');

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

    /**
     * @test
     */
    public function itFindsArraySymbols(): void
    {
        /**
         * TODO find a better strategy for testing this scenario
         */
        $this->expectNotToPerformAssertions();
        $this->loadConsumedFileSymbols(self::ARRAY_NAMESPACE, [AutoloadType::FILES]);
    }
}
