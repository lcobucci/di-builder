<?php
namespace Lcobucci\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * The dependency injection XML builder
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class XmlContainerBuilder extends ContainerBuilder
{
    /**
     * Returns the file loader
     *
     * @param SymfonyBuilder $container
     * @param array $path
     *
     * @return XmlFileLoader
     */
    protected function getLoader(SymfonyBuilder $container, array $path)
    {
        return new XmlFileLoader(
            $container,
            new FileLocator($path)
        );
    }
}
