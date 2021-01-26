<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration\Symbol\Loader;

use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use ComposerUnused\SymbolParser\File\FileContentProvider;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use ComposerUnused\SymbolParser\Symbol\Loader\FileSymbolLoader;
use ComposerUnused\SymbolParser\Symbol\Provider\FileSymbolProvider;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;
use ComposerUnused\SymbolParser\Test\Integration\AbstractIntegrationTestCase;
use Generator;
use PhpParser\ParserFactory;
use function array_merge;
use function iterator_to_array;

class ClassmapAutoloadTest extends AbstractIntegrationTestCase
{
    private const BASE_DIR = __DIR__ . '/../../../assets/TestProjects/ClassmapAutoload';

    /**
     * @test
     */
    public function itShouldLoadRootSymbolsCorrectly(): void
    {
        $rootPackage = $this->loadRootPackage();
        /** @var array<SymbolInterface> $symbols */
        $symbols = iterator_to_array($this->collectSymbols(self::BASE_DIR, $rootPackage));

        self::assertCount(3, $symbols);
        self::assertArrayHasKey('ClassmapAutoload\Addon\Parsed\Lib\ParsedClass', $symbols);
        self::assertArrayHasKey('ClassmapAutoload\ParsedClass', $symbols);
        self::assertArrayHasKey('ClassmapAutoload\Redis\MyRedis', $symbols);
    }

    /**
     * @test
     */
    public function itShouldLoadForeignSymbolsCorrectly(): void
    {
        $rootPackage = $this->loadRootPackage();
        $requiredSymbols = [];

        foreach ($rootPackage->getRequires() as $require) {
            $composerPackage = $rootPackage->getRepository()->findPackage(
                $require->getTarget(),
                $require->getConstraint()
            );

            if ($composerPackage === null) {
                continue;
            }

            $symbols = $this->collectSymbols(self::BASE_DIR . '/vendor/' . $require->getTarget(), $composerPackage);
            $requiredSymbols[] = iterator_to_array($symbols);
        }

        $requiredSymbols = array_merge(...$requiredSymbols);
        self::assertCount(2, $requiredSymbols);
    }

    /**
     * @return Generator<SymbolInterface>
     */
    private function collectSymbols(string $baseDir, PackageInterface $package): Generator
    {
        return (new FileSymbolLoader(
            $baseDir,
            new FileSymbolProvider(
                new SymbolNameParser(
                    (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
                    new DefinedSymbolCollector()
                ),
                new FileContentProvider()
            ),
            ['classmap']
        ))->load($package);
    }

    private function loadRootPackage(): RootPackageInterface
    {
        $composer = $this->getComposer(self::BASE_DIR);
        $rootPackage = $composer->getPackage();

        $rootPackage->setRepository(
            $composer->getRepositoryManager()->getLocalRepository()
        );

        return $rootPackage;
    }
}
