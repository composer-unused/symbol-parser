<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use ArrayIterator;
use ComposerUnused\SymbolParser\Symbol\Provider\FileSymbolProvider;
use Generator;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use SplFileInfo;

final class SymbolNameParser implements SymbolNameParserInterface
{
    /** @var Parser */
    private $parser;
    /** @var NodeTraverser */
    private $traverser;
    /** @var SymbolCollectorInterface */
    private $visitor;
    /** @var FileSymbolProvider */
    private $fileProvider;
    /** @var SplFileInfo */
    private $currentFile;

    public function __construct(Parser $parser, SymbolCollectorInterface $visitor)
    {
        $this->parser = $parser;
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($visitor);

        $this->visitor = $visitor;
    }

    /**
     * @return Generator<string>
     */
    public function parseSymbolNames(string $code): Generator
    {
        $nodes = $this->parser->parse($code);

        if ($nodes === null) {
            return;
        }

        $this->visitor->followIncludesCallback(function (string $includeFileName) {
            $this->fileProvider->addFiles(new ArrayIterator([
                new SplFileInfo($this->currentFile->getPath() . '/' . $includeFileName)
            ]));
        });

        $this->traverser->traverse($nodes);

        yield from $this->visitor->getSymbolNames();
        $this->visitor->reset();
    }

    public function setSymbolProvider(FileSymbolProvider $provider): void
    {
        $this->fileProvider = $provider;
    }

    public function setCurrentFile(SplFileInfo $file): void
    {
        $this->currentFile = $file;
    }
}
