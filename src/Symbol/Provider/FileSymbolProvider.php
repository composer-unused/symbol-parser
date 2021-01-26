<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Symbol\Provider;

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

    public function __construct(SymbolNameParserInterface $parser, FileContentProvider $fileContentProvider)
    {
        $this->parser = $parser;
        $this->fileContentProvider = $fileContentProvider;
    }

    /**
     * @param array<SplFileInfo> $files
     *
     * @return Generator<string, SymbolInterface>
     * @throws IOException
     */
    public function provide(iterable $files): Generator
    {
        foreach ($files as $file) {
            $content = $this->fileContentProvider->getContent($file);

            foreach ($this->parser->parseSymbolNames($content) as $symbolName) {
                yield $symbolName => new Symbol($symbolName);
            }
        }
    }
}
