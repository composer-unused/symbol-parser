<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Stubs;

use ComposerUnused\Contracts\LinkInterface;
use ComposerUnused\Contracts\PackageInterface;

final class TestPackage implements PackageInterface
{
    /** @phpstan-var array{psr-0?: array<string, string|string[]>, psr-4?: array<string, string|string[]>, classmap?: list<string>, files?: list<string>} */
    public array $autoload = [];
    public string $name = '';
    /** @var array<LinkInterface> */
    public array $requires = [];
    /** @var array<string> */
    public array $suggests = [];

    public function getAutoload(): array
    {
        return $this->autoload;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequires(): array
    {
        return $this->requires;
    }

    public function getSuggests(): array
    {
        return $this->suggests;
    }
}
