<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use PhpParser\NodeVisitor;

interface SymbolCollectorInterface extends NodeVisitor
{
    /**
     * @return array<string>
     */
    public function getSymbolNames(): array;

    /**
     * Reset all previously found symbols
     */
    public function reset(): void;
}
