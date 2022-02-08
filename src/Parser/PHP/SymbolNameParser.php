<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use ArrayIterator;
use Closure;
use ComposerUnused\SymbolParser\Symbol\Provider\FileIterationInterface;
use Generator;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use SplFileInfo;

final class SymbolNameParser implements SymbolNameParserInterface
{
    private Parser $parser;
    private NodeTraverser $traverser;
    private SymbolCollectorInterface $visitor;
    private ?FileIterationInterface $fileIterator = null;
    private ?SplFileInfo $currentFile = null;

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
        try {
            $nodes = $this->parser->parse($code);
        } catch (Error $parseError) {
            // TODO catch exception
            return null;
        }

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
        if ($this->fileIterator === null || $this->currentFile === null) {
            return;
        }

        $file = new SplFileInfo($this->currentFile->getPath() . '/' . ltrim($fileInclude->getPath(), '/'));
        $this->fileIterator->appendFiles(new ArrayIterator([$file]));
    }
}
