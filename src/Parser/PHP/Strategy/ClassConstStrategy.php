<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP\Strategy;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;

use function sprintf;

final class ClassConstStrategy implements StrategyInterface
{
    public function canHandle(Node $node): bool
    {
        if (!$node instanceof ClassConstFetch) {
            return false;
        }

        if (!$node->class instanceof Node\Name) {
            return false;
        }

        if (!$node->name instanceof Node\Identifier) {
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
        return [sprintf('%s\%s', $node->class, $node->name)];
    }
}
