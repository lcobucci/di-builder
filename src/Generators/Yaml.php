<?php
namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The dependency injection generator for YAML files
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class Yaml extends Generator
{
    /**
     * {@inheritdoc}
     */
    public function getLoader(SymfonyBuilder $container, array $paths)
    {
        return new YamlFileLoader(
            $container,
            new FileLocator($paths)
        );
    }
}
