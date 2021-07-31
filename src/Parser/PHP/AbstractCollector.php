<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use Closure;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractCollector extends NodeVisitorAbstract implements SymbolCollectorInterface
{
    /** @var null|Closure */
    private $includeCallback;

    public function followIncludesCallback(Closure $includeCallback): void
    {
        $this->includeCallback = $includeCallback;
    }

    protected function followIncludes(Node $node): void
    {
        if ($this->includeCallback === null) {
            return;
        }

        if (!$node instanceof Node\Stmt\Expression) {
            return;
        }

        $include = $node->expr;
        if (!$include instanceof Node\Expr\Include_) {
            return;
        }

        $scalar = $include->expr;
        if (!$scalar instanceof Node\Scalar\String_) {
            return;
        }

        ($this->includeCallback)($scalar->value);
    }
}
