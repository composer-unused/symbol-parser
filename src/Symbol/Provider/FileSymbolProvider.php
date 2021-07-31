<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Symbol\Provider;

use ArrayIterator;
use ComposerUnused\SymbolParser\Exception\IOException;
use ComposerUnused\SymbolParser\File\FileContentProvider;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParserInterface;
use ComposerUnused\SymbolParser\Symbol\Symbol;
use ComposerUnused\SymbolParser\Symbol\SymbolInterface;
use Generator;
use SplFileInfo;

class FileSymbolProvider
{
    /** @var SymbolNameParserInterface */
    private $parser;
    /** @var FileContentProvider */
    private $fileContentProvider;
    /** @var array<iterable<SplFileInfo>>  */
    private $additionalFiles = [];

    public function __construct(SymbolNameParserInterface $parser, FileContentProvider $fileContentProvider)
    {
        $this->parser = $parser;
        $this->fileContentProvider = $fileContentProvider;
    }

    /**
     * @param iterable<SplFileInfo> $files
     *
     * @return Generator<string, SymbolInterface>
     * @throws IOException
     */
    public function provide(iterable $files): Generator
    {
        $this->parser->setSymbolProvider($this);

        foreach ($this->iterateFiles($files) as $file) {
            $content = $this->fileContentProvider->getContent($file);
            $this->parser->setCurrentFile($file);

            foreach ($this->parser->parseSymbolNames($content) as $symbolName) {
                yield $symbolName => new Symbol($symbolName);
            }
        }
    }

    /**
     * @param iterable<SplFileInfo> $files
     * @return Generator<SplFileInfo>
     */
    private function iterateFiles(iterable $files): Generator
    {
        yield from $files;

        foreach ($this->additionalFiles as $additionalFile) {
            yield from $additionalFile;
        }
    }

    /**
     * @param iterable<SplFileInfo> $files
     */
    public function addFiles(iterable $files): void
    {
        $this->additionalFiles[] = $files;
    }
}
