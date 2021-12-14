<?php

declare(strict_types=1);

namespace Icanhazstring\Composer\Test\Unused\Unit\Parser\PHP;

use ComposerUnused\SymbolParser\Parser\PHP\ConsumedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\FunctionInvocationStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class SymbolNameParserTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldParseClasses(): void
    {
        $code = <<<CODE
        <?php

        namespace Test;

        use Nette\Database;

        final class MyClass {}
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new DefinedSymbolCollector()
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertCount(1, $symbols);
        self::assertSame('Test\MyClass', $symbols[0]);
    }

    /**
     * @test
     */
    public function itShouldParseDefinedFunctions(): void
    {
        $code = <<<CODE
        <?php

        function testfunction() {}
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new DefinedSymbolCollector()
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertCount(1, $symbols);
        self::assertSame('testfunction', $symbols[0]);
    }

    /**
     * @test
     */
    public function itShouldParseConsumedFunctions(): void
    {
        $code = <<<CODE
        <?php

        testfunction();
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new ConsumedSymbolCollector([new FunctionInvocationStrategy()])
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertCount(1, $symbols);
        self::assertSame('testfunction', $symbols[0]);
    }
}
