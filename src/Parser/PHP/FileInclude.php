<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\String_;

final class FileInclude
{
    /** @var string */
    private $filePath;

    private function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    private static function resolveConcatOperation(Concat $concat): string
    {
        $subPath = '';
        /** @var String_ $rightOperand */
        $rightOperand = $concat->right;

        if ($concat->left instanceof Concat) {
            $subPath = self::resolveConcatOperation($concat->left);
        }

        return $subPath . $rightOperand->value;
    }

    public static function fromConcatOperation(Concat $concat): self
    {
        return new self(self::resolveConcatOperation($concat));
    }

    public static function fromScalar(String_ $scalar): self
    {
        return new self($scalar->value);
    }

    public function getPath(): string
    {
        return $this->filePath;
    }
}
