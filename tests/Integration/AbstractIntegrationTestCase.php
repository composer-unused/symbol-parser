<?php

declare(strict_types=1);

namespace ComposerUnused\SymbolParser\Test\Integration;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Package\PackageInterface;
use PHPUnit\Framework\TestCase;

class AbstractIntegrationTestCase extends TestCase
{
    protected function getComposer(string $cwd): Composer
    {
        return (new Factory())->createComposer(new NullIO(), $cwd . '/composer.json', true, $cwd, false);
    }

    protected function loadPackage(string $cwd, string $packageName): PackageInterface
    {
        $composer = $this->getComposer($cwd);

        $testDependency = $composer->getPackage()->getRequires()[$packageName];
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        /** @var string $constraint */
        $constraint = $testDependency->getConstraint();

        /** @var PackageInterface $package */
        $package = $localRepo->findPackage(
            $testDependency->getTarget(),
            $constraint
        );

        return $package;
    }
}
