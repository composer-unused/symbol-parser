<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Unit\Parser\PHP;

use ComposerUnused\SymbolParser\Parser\PHP\Strategy\ExtendsParseStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\ImplementsParseStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\UseStrategy;
use ComposerUnused\SymbolParser\Test\ParserTestCase;

final class SymbolNameParserTest extends ParserTestCase
{
    /**
     * @test
     */
    public function itShouldParseDefinedSymbols(): void
    {
        $code = <<<CODE
        <?php

        namespace Test;

        use Nette\Database;

        final class MyClass {}
        CODE;

        $symbols = $this->parseDefinedSymbols($code);

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

        $symbols = $this->parseDefinedSymbols($code);

        self::assertCount(1, $symbols);
        self::assertSame('testfunction', $symbols[0]);
    }

    /**
     * @test
     */
    public function itShouldConsolidateSymbols(): void
    {
        $code = <<<CODE
        <?php

        namespace Testing;

        use My\NameSpace1; // partial foreign namespace use
        use B\NS\MyClass; // foreign FQN

        class Foo extends MyClass implements NameSpace1\Bar, \Other\Namespace2\Baz {
        }
        CODE;

        $symbols = $this->parseConsumedSymbols(
            [
                new UseStrategy(),
                new ExtendsParseStrategy(),
                new ImplementsParseStrategy(),
            ],
            $code
        );

        self::assertCount(4, $symbols);
        self::assertSame('My\NameSpace1', $symbols[0]);
        self::assertSame('B\NS\MyClass', $symbols[1]);
        self::assertSame('My\NameSpace1\Bar', $symbols[2]);
        self::assertSame('Other\Namespace2\Baz', $symbols[3]);
    }

    /**
     * @test
     */
    public function itShouldNotConsolidateDifferentNamespaces(): void
    {
        $code = <<<CODE
        <?php

        use My\Space\Using\ForeignUtility;
        use ForeignUtility\SpecialClass;

        CODE;

        $symbols = $this->parseConsumedSymbols([new UseStrategy()], $code);

        self::assertCount(2, $symbols);
        self::assertSame('My\Space\Using\ForeignUtility', $symbols[0]);
        self::assertSame('ForeignUtility\SpecialClass', $symbols[1]);
    }
}
