<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test;

use ComposerUnused\SymbolParser\Parser\PHP\ConsumedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\StrategyInterface;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

class ParserTestCase extends TestCase
{
    /**
     * @param array<StrategyInterface> $strategies
     * @return array<string>
     */
    public function parseConsumedSymbols(array $strategies, string $code): array
    {
        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new ConsumedSymbolCollector($strategies)
        );

        return iterator_to_array($symbolNameParser->parseSymbolNames($code));
    }

    /**
     * @return array<string>
     */
    public function parseDefinedSymbols(string $code): array
    {
        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new DefinedSymbolCollector()
        );

        return iterator_to_array($symbolNameParser->parseSymbolNames($code));
    }
}
