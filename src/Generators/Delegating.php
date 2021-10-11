<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The dependency injection generator that allows XML, YAML and PHP files
 */
final class Delegating extends Generator
{
    /** @inheritDoc */
    public function getLoader(SymfonyBuilder $container, array $paths): LoaderInterface
    {
        $locator = new FileLocator($paths);

        return new DelegatingLoader(
            new LoaderResolver(
                [
                    new XmlFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                    new PhpFileLoader($container, $locator),
                ],
            ),
        );
    }
}
