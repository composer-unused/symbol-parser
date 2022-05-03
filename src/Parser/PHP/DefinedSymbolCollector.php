<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use PhpParser\Node;
use PhpParser\NodeTraverser;

use function array_merge;

/**
 * Collect defined symbols.
 *
 * Define symbols, are symbols provided by a dependency. These are symbols you will
 * get from a required package that are ONLY defined by that package.
 *
 * These might be classes, functions or constants
 */
final class DefinedSymbolCollector extends AbstractCollector
{
    private string $namespace = '';

    /** @var array<string> */
    private array $functions = [];
    /** @var array<string> */
    private array $constants = [];
    /** @var array<string> */
    private array $classes = [];

    public function enterNode(Node $node)
    {
        $this->followIncludes($node);

        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name . '\\';
            return null;
        }

        if (
            $node instanceof Node\Stmt\Class_ ||
            $node instanceof Node\Stmt\Interface_
        ) {
            $this->classes[] = $this->namespace . $node->name;

            // We only need the class name, no need to dig further into the class
            // as there is no more symbol to be defined which can't be checked against
            // the class name already (e.g. public constants)
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Node\Stmt\Function_) {
            $this->functions[] = $this->namespace . $node->name;
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Node\Const_) {
            $this->constants[] = $this->namespace . $node->name;
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Node\Stmt\Expression && $node->expr instanceof Node\Expr\FuncCall) {
            /** @var Node\Name $expressionName */
            $expressionName = $node->expr->name;
            $functionName = $expressionName->parts[0] ?? null;
            $firstArgument = $node->expr->args[0];
            assert($firstArgument instanceof Node\Arg);

            $firstArgumentValue = $firstArgument->value;
            if ($functionName === 'define' && $firstArgumentValue instanceof Node\Scalar\String_) {
                $this->constants[] = $firstArgumentValue->value;
            }

            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    public function reset(): void
    {
        $this->classes = [];
        $this->constants = [];
        $this->functions = [];
        $this->namespace = '';
    }

    public function getSymbolNames(): array
    {
        return array_merge(
            $this->classes,
            $this->functions,
            $this->constants
        );
    }
}
