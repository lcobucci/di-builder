<?php
namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * The dependency injection generator for XML files
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class Xml extends Generator
{
    /**
     * {@inheritdoc}
     */
    public function getLoader(SymfonyBuilder $container, array $paths)
    {
        return new XmlFileLoader(
            $container,
            new FileLocator($paths)
        );
    }
}
