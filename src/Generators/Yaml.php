<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The dependency injection generator for YAML files
 *
 * @internal
 */
final readonly class Yaml extends Generator
{
    /** @inheritDoc */
    public function getLoader(SymfonyBuilder $container, array $paths, ?string $profileName = null): LoaderInterface
    {
        return new YamlFileLoader(
            $container,
            new FileLocator($paths),
            $profileName,
        );
    }
}
