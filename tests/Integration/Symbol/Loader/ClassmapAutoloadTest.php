<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration\Symbol\Loader;

use Composer\Repository\RepositoryInterface;
use ComposerUnused\SymbolParser\Parser\PHP\AutoloadType;
use ComposerUnused\SymbolParser\Test\Integration\AbstractIntegrationTestCase;

use function array_merge;

class ClassmapAutoloadTest extends AbstractIntegrationTestCase
{
    private const BASE_DIR = __DIR__ . '/../../../assets/TestProjects/ClassmapAutoload';

    /**
     * @test
     */
    public function itShouldLoadRootSymbolsCorrectly(): void
    {
        $symbols = $this->loadDefinedFileSymbols(self::BASE_DIR, [AutoloadType::CLASSMAP]);

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
        $rootPackage = $this->loadRootPackage(self::BASE_DIR);
        $requiredSymbols = [];

        foreach ($rootPackage->getRequires() as $require) {
            $repository = $rootPackage->getRepository();
            assert($repository instanceof RepositoryInterface);

            $composerPackage = $repository->findPackage(
                $require->getTarget(),
                $require->getConstraint()
            );

            if ($composerPackage === null) {
                continue;
            }

            $requiredSymbols[] = $this->loadDefinedFileSymbols(
                self::BASE_DIR,
                [AutoloadType::CLASSMAP],
                $composerPackage->getName()
            );
        }

        $requiredSymbols = array_merge(...$requiredSymbols);
        self::assertCount(2, $requiredSymbols);
    }
}
