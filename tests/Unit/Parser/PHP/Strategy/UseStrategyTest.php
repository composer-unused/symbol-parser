<?php

namespace ComposerUnused\SymbolParser\Test\Unit\Parser\PHP\Strategy;

use ComposerUnused\SymbolParser\Parser\PHP\Strategy\UseStrategy;
use ComposerUnused\SymbolParser\Test\ParserTestCase;

/**
 * Introduces unit tests for UseStrategy
 * Superseded to https://github.com/composer-unused/symbol-parser/pull/102
 *
 * @link https://github.com/composer-unused/symbol-parser/issues/142
 * @author Laurent Laville
 */
class UseStrategyTest extends ParserTestCase
{
    private UseStrategy $strategy;

    protected function setUp(): void
    {
        $this->strategy = new UseStrategy();
    }

    /**
     * @test
     */
    public function itShouldParseSymbolFromUseImports(): void
    {
        $code = <<<CODE
        <?php
        use My\Space\Using\ForeignUtility;
        use ForeignUtility\SpecialClass;
        CODE;

        $symbols = $this->parseConsumedSymbols([$this->strategy], $code);

        self::assertCount(2, $symbols);
        self::assertSame('My\Space\Using\ForeignUtility', $symbols[0]);
        self::assertSame('ForeignUtility\SpecialClass', $symbols[1]);
    }
}
