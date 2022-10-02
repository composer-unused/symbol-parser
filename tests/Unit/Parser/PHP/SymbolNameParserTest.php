<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Unit\Parser\PHP;

use ComposerUnused\SymbolParser\Parser\PHP\ConsumedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\DefinedSymbolCollector;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\AnnotationStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\ExtendsParseStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\FullQualifiedParameterStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\FunctionInvocationStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\ImplementsParseStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\TypedAttributeStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\Strategy\UseStrategy;
use ComposerUnused\SymbolParser\Parser\PHP\SymbolNameParser;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
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

    /**
     * @test
     */
    public function itShouldConsolidatedSymbols(): void
    {
        $code = <<<CODE
        <?php

        namespace Testing;

        use My\NameSpace1; // partial foreign namespace use
        use B\NS\MyClass; // foreign FQN

        class Foo extends MyClass implements NameSpace1\Bar, \Other\Namespace2\Baz {
        }
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new ConsumedSymbolCollector(
                [
                    new UseStrategy(),
                    new ExtendsParseStrategy(),
                    new ImplementsParseStrategy(),
                ]
            )
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertCount(3, $symbols);
        self::assertSame('My\NameSpace1\Bar', $symbols[0]);
        self::assertSame('B\NS\MyClass', $symbols[1]);
        self::assertSame('Other\Namespace2\Baz', $symbols[2]);
    }

    /**
     * @test
     */
    public function itShouldParseSymbolFromTypedAttribute(): void
    {
        $code = <<<CODE
        <?php

        namespace Testing;

        use Other\Fubar;

        class Foo {
            private \$foo;
            private Fubar \$baz;
            private \My\Namespace\Bar \$bar;
        }
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new ConsumedSymbolCollector(
                [
                    new TypedAttributeStrategy(),
                ]
            )
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertCount(1, $symbols);
        self::assertSame('My\Namespace\Bar', $symbols[0]);
    }

    /**
     * @test
     */
    public function itShouldParseFQNFunctionParameter(): void
    {
        $code = <<<CODE
        <?php

        namespace Testing;

        class Foo {
            public function __construct(private readonly \My\Namespace\Bar1 \$parameter) {}
            public function test(\My\Namespace\Bar2 \$parameter) {}
        }
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new ConsumedSymbolCollector(
                [
                    new FullQualifiedParameterStrategy()
                ]
            )
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertCount(2, $symbols);
        self::assertSame('My\Namespace\Bar1', $symbols[0]);
        self::assertSame('My\Namespace\Bar2', $symbols[1]);
    }

    /**
     * @test
     */
    public function itShouldParseAnnotations(): void
    {
        $code = <<<CODE
        <?php

        namespace Test;

        /** @My\Namespace\Foo */
        final class MyClass
        {
            /** @My\Namespace\Bar */
            private int \$x;

            /** @My\Namespace\Bat */
            public function bar(): void {}
        }
        CODE;

        $symbolNameParser = new SymbolNameParser(
            (new ParserFactory())->create(ParserFactory::ONLY_PHP7),
            new ConsumedSymbolCollector(
                [
                    new AnnotationStrategy(
                        new ConstExprParser(),
                        new Lexer()
                    ),
                ]
            )
        );

        $symbols = iterator_to_array($symbolNameParser->parseSymbolNames($code));

        self::assertSame(
            [
                'My\Namespace\Foo',
                'My\Namespace\Bar',
                'My\Namespace\Bat',
            ],
            $symbols
        );
    }
}
