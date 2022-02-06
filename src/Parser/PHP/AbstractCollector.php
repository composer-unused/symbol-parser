<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use Closure;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

abstract class AbstractCollector extends NodeVisitorAbstract implements SymbolCollectorInterface
{
    private ?Closure $includeCallback = null;

    public function setFileIncludeCallback(Closure $includeCallback): void
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

        $expr = $include->expr;

        switch (get_class($expr)) {
            case Node\Scalar\String_::class:
                ($this->includeCallback)(FileInclude::fromScalar($expr));
                break;
            case Node\Expr\BinaryOp\Concat::class:
                ($this->includeCallback)(FileInclude::fromConcatOperation($expr));
                break;
        }
    }
}
