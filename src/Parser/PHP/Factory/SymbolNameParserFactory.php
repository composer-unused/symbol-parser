<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP\Factory;

use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParserInterface;
use ComposerUnused\SymbolParser\Parser\PHP\ConsumedSymbolCollector;
use PhpParser\ParserFactory;
use Psr\Container\ContainerInterface;

final class SymbolNameParserFactory
{
    public function __invoke(ContainerInterface $container): SymbolNameParserInterface
    {
        return new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            $container->get(ConsumedSymbolCollector::class)
        );
    }
}
