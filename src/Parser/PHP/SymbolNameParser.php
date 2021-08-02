<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use ArrayIterator;
use Closure;
use ComposerUnused\SymbolParser\Symbol\Provider\FileIterationInterface;
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
    /** @var FileIterationInterface */
    private $fileIterator;
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

        if ($this->fileIterator !== null) {
            $this->visitor->setFileIncludeCallback(Closure::fromCallable([$this, 'handleFileInclude']));
        }

        $this->traverser->traverse($nodes);

        yield from $this->visitor->getSymbolNames();
        $this->visitor->reset();
    }

    public function setFileIterator(FileIterationInterface $fileIterator): void
    {
        $this->fileIterator = $fileIterator;
    }

    public function setCurrentFile(SplFileInfo $file): void
    {
        $this->currentFile = $file;
    }

    public function handleFileInclude(FileInclude $fileInclude): void
    {
        $file = new SplFileInfo($this->currentFile->getPath() . '/' . ltrim($fileInclude->getPath(), '/'));
        $this->fileIterator->appendFiles(new ArrayIterator([$file]));
    }
}
