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
            $autoloadDefinition = $package->getAutoload()[$autoloadType] ?? [];
            $autoloadPaths = $this->normalizePsrStructure($autoloadDefinition);
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
     * @param array<string, list<string>> $paths
     * @return array<string, list<string>>
     */
    private function resolvePackageSourcePath(array $paths): array
    {
        $fullPaths = [];

        foreach ($paths as $namespace => $namespacePaths) {
            $fullPaths[$namespace] = array_map(
                fn (string $path): string => $this->baseDir . DIRECTORY_SEPARATOR . $path,
                $namespacePaths
            );
        }

        return $fullPaths;
    }

    /**
     * @param array<string, list<string>> $classmapPaths
     * @return array<array<string>>
     */
    private function partitionFilesAndFolders(array $classmapPaths): array
    {
        $files = [];
        $folders = [];

        foreach ($classmapPaths as $namespacePaths) {
            foreach ($namespacePaths as $path) {
                if ($this->isFilePath($path)) {
                    $files[] = $path;
                } else {
                    $folders[] = $path;
                }
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

    /**
     * @param array<array-key, string|list<string>> $autoloadDefinition
     *
     * @return array<array-key, list<string>>
     */
    private function normalizePsrStructure(array $autoloadDefinition): array
    {
        return array_map(
            static fn ($value): array => is_array($value) ? $value : [$value],
            $autoloadDefinition
        );
    }
}
