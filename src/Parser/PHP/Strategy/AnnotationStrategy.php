<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP\Strategy;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeForParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\OffsetAccessTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
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

        return $this->flattenArray(
            array_map(
                function (PhpDocTagNode $n): array {
                    if ($n->value instanceof GenericTagValueNode) {
                        return [ltrim($n->name, " \t\n\r\0\x0B@")];
                    }

                    return $this->extractSymbolNamesFromPhpDocNode($n->value);
                },
                $phpDocTagNodes
            )
        );
    }

    /**
     * @param array<\PHPStan\PhpDocParser\Ast\Node> $nodes
     *
     * @return array<string>
     */
    private function extractSymbolNamesFromPhpDocNodeIterable(array $nodes): array
    {
        return $this->flattenArray(
            array_map(
                [$this, 'extractSymbolNamesFromPhpDocNode'],
                $nodes
            )
        );
    }

    /**
     * @return array<string>
     */
    private function extractSymbolNamesFromPhpDocNode(?\PHPStan\PhpDocParser\Ast\Node $node): array
    {
        if ($node === null) {
            return [];
        }

        switch (get_class($node)) {
            case IdentifierTypeNode::class:
                return [$node->name];
            case NullableTypeNode::class:
                return $this->extractSymbolNamesFromPhpDocNode($node->type);
            case MethodTagValueNode::class:
            case CallableTypeNode::class:
                return array_merge(
                    $this->extractSymbolNamesFromPhpDocNode($node->returnType),
                    $this->extractSymbolNamesFromPhpDocNodeIterable($node->parameters)
                );
            case GenericTypeNode::class:
                return array_merge(
                    $this->extractSymbolNamesFromPhpDocNode($node->type),
                    $this->extractSymbolNamesFromPhpDocNodeIterable($node->genericTypes)
                );
            case ArrayShapeNode::class:
                $symbols = [];
                foreach ($node->items as $item) {
                    $symbols[] = array_merge($symbols, $this->extractSymbolNamesFromPhpDocNode($item->valueType));
                }
                return $symbols;
            case UnionTypeNode::class:
            case IntersectionTypeNode::class:
                return $this->extractSymbolNamesFromPhpDocNodeIterable($node->types);
            case ConditionalTypeForParameterNode::class:
            case ConditionalTypeNode::class:
                return array_merge(
                    $this->extractSymbolNamesFromPhpDocNode($node->targetType),
                    $this->extractSymbolNamesFromPhpDocNode($node->if),
                    $this->extractSymbolNamesFromPhpDocNode($node->else),
                );
            case OffsetAccessTypeNode::class:
                return array_merge(
                    $this->extractSymbolNamesFromPhpDocNode($node->type),
                    $this->extractSymbolNamesFromPhpDocNode($node->offset),
                );
        }

        if (property_exists($node, 'type')) {
            return $this->extractSymbolNamesFromPhpDocNode($node->type);
        }

        return [];
    }

    /**
     * @template T
     * @param array<T|array<T>> $array
     * @return array<T>
     */
    private function flattenArray(array $array): array
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

        return iterator_to_array($iterator, false);
    }
}
