<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * The dependency injection generator for PHP files
 */
final class Php extends Generator
{
    /** @inheritDoc */
    public function getLoader(SymfonyBuilder $container, array $paths): LoaderInterface
    {
        return new PhpFileLoader(
            $container,
            new FileLocator($paths)
        );
    }
}
