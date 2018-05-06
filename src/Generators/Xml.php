<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * The dependency injection generator for XML files
 */
final class Xml extends Generator
{
    /**
     * {@inheritdoc}
     */
    public function getLoader(SymfonyBuilder $container, array $paths): LoaderInterface
    {
        return new XmlFileLoader(
            $container,
            new FileLocator($paths)
        );
    }
}
