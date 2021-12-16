<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
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
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class AbstractIntegrationTestCase extends TestCase
{
    protected function getComposer(string $cwd): Composer
    {
        return (new Factory())->createComposer(new NullIO(), $cwd . '/composer.json', true, $cwd, false);
    }

    private function loadPackage(string $cwd, string $packageName): PackageInterface
    {
        $composer = $this->getComposer($cwd);

        $testDependency = $composer->getPackage()->getRequires()[$packageName];
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        /** @var string $constraint */
        $constraint = $testDependency->getConstraint();

        /** @var PackageInterface $package */
        $package = $localRepo->findPackage(
            $testDependency->getTarget(),
            $constraint
        );

        return $package;
    }

    /**
     * @param list<string> $autoloadTypes
     * @return array<SymbolInterface>
     */
    protected function loadDefinedFileSymbols(string $baseDir, array $autoloadTypes = null, string $packageName = null): array
    {
        if ($packageName === null) {
            $package = $this->loadRootPackage($baseDir);
        } else {
            $package = $this->loadPackage($baseDir, $packageName);
            $baseDir .= '/vendor/' . $package->getName();
        }

        if ($autoloadTypes === null) {
            $autoloadTypes = AutoloadType::all();
        }

        $fileLoader = new FileSymbolLoader(
            new FileSymbolProvider(
                new SymbolNameParser(
                    (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
                    new DefinedSymbolCollector()
                ),
                new FileContentProvider()
            ),
            $autoloadTypes
        );

        return iterator_to_array(
            $fileLoader->withBaseDir($baseDir)->load($package)
        );
    }

    /**
     * @param list<string> $autoloadTypes
     * @return array<SymbolInterface>
     */
    protected function loadConsumedFileSymbols(string $baseDir, array $autoloadTypes = null): array
    {
        $rootPackage = $this->getComposer($baseDir)->getPackage();

        if ($autoloadTypes === null) {
            $autoloadTypes = AutoloadType::all();
        }

        $fileLoader = new FileSymbolLoader(
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
                            ),
                            new FunctionInvocationStrategy()
                        ]
                    )
                ),
                new FileContentProvider()
            ),
            $autoloadTypes
        );

        return iterator_to_array(
            $fileLoader->withBaseDir($baseDir)->load($rootPackage)
        );
    }

    protected function loadRootPackage(string $baseDir): RootPackageInterface
    {
        $composer = $this->getComposer($baseDir);
        $rootPackage = $composer->getPackage();

        $rootPackage->setRepository(
            $composer->getRepositoryManager()->getLocalRepository()
        );

        return $rootPackage;
    }
}
