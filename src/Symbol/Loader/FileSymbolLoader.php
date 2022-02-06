<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Symbol\Loader;

use ComposerUnused\Contracts\PackageInterface;
use ComposerUnused\SymbolParser\Symbol\Provider\FileSymbolProvider;
use Generator;
use Symfony\Component\Finder\Finder;

use function array_map;
use function array_merge;
use function preg_match;

final class FileSymbolLoader implements SymbolLoaderInterface
{
    private FileSymbolProvider $fileSymbolProvider;
    /** @var array<string> */
    private array $autoloadTypes;
    private ?string $baseDir;

    /**
     * @param array<string> $autoloadTypes
     */
    public function __construct(FileSymbolProvider $fileSymbolProvider, array $autoloadTypes)
    {
        $this->fileSymbolProvider = $fileSymbolProvider;
        $this->autoloadTypes = $autoloadTypes;
    }

    public function load(PackageInterface $package): Generator
    {
        $paths = [];

        foreach ($this->autoloadTypes as $autoloadType) {
            /** @var array<string, string> $autoloadPaths */
            $autoloadPaths = $package->getAutoload()[$autoloadType] ?? [];
            $paths[] = $this->resolvePackageSourcePath($autoloadPaths);
        }

        [$sourceFiles, $sourceFolders] = $this->partitionFilesAndFolders(
            array_merge(...$paths)
        );

        $finder = new Finder();

        $files = $finder
            ->files()
            ->name('*.php')
            ->in($sourceFolders)
            ->append($sourceFiles)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->ignoreUnreadableDirs()
            ->followLinks()
            ->exclude(['vendor']);

        $this->fileSymbolProvider->appendFiles($files->getIterator());

        yield from $this->fileSymbolProvider->provide();
    }

    /**
     * @param array<string, string> $paths
     * @return array<string, string>
     */
    private function resolvePackageSourcePath(array $paths): array
    {
        return array_map(function (string $path) {
            return $this->baseDir . DIRECTORY_SEPARATOR . $path;
        }, $paths);
    }

    /**
     * @param array<string> $classmapPaths
     * @return array<array<string>>
     */
    private function partitionFilesAndFolders(array $classmapPaths): array
    {
        $files = [];
        $folders = [];

        foreach ($classmapPaths as $path) {
            if ($this->isFilePath($path)) {
                $files[] = $path;
            } else {
                $folders[] = $path;
            }
        }

        return [$files, $folders];
    }

    private function isFilePath(string $path): bool
    {
        return (bool)preg_match('/\.[a-z0-9]+$/i', $path);
    }

    public function withBaseDir(?string $baseDir): SymbolLoaderInterface
    {
        $clone = clone $this;
        $clone->baseDir = $baseDir;

        return $clone;
    }
}
