<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP\Strategy;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

final class AnnotationStrategy implements StrategyInterface
{
    private Lexer $lexer;
    private PhpDocParser $phpDocParser;

    public function __construct(
        ConstExprParser $constExprParser,
        Lexer $lexer
    ) {
        $this->lexer = $lexer;
        $typeParser = new TypeParser($constExprParser);
        $this->phpDocParser = new PhpDocParser($typeParser, $constExprParser);
    }

    public function canHandle(Node $node): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        return true;
    }

    /**
     * @param Node&ClassConstFetch $node
     * @return array<string>
     */
    public function extractSymbolNames(Node $node): array
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }

        $phpDoc = $this->phpDocParser->parse(
            new TokenIterator($this->lexer->tokenize($docComment->getText()))
        );

        $phpDocTagNodes = array_filter(
            $phpDoc->children,
            function (PhpDocChildNode $node): bool {
                return $node instanceof PhpDocTagNode;
            }
        );

        return array_map(
            function (PhpDocTagNode $n): string {
                return ltrim($n->name, " \t\n\r\0\x0B@");
            },
            $phpDocTagNodes
        );
    }
}
