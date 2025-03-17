<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Parser\PHP;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

use function strtolower;

/**
 * @author Laurent Laville
 */
class NameContext extends \PhpParser\NameContext
{
    public function getResolvedName(Name $name, int $type): ?Name
    {
        if ($type === Stmt\Use_::TYPE_NORMAL && $name->isSpecialClassName()) {
            if (property_exists($name, 'name')) {
                $className = $name->name;
            } else {
                $className = $name->getParts()[0];
            }

            return $this->aliases[$type][strtolower($className)] ?? null;
        }
        return parent::getResolvedName($name, $type);
    }
}
