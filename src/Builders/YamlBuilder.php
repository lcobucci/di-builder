<?php
namespace Lcobucci\DependencyInjection\Builders;

use Lcobucci\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The dependency injection builder for YAML files
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class YamlBuilder extends ContainerBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function getLoader(SymfonyBuilder $container, array $path)
    {
        return new YamlFileLoader(
            $container,
            new FileLocator($path)
        );
    }
}
