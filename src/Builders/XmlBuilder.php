<?php
namespace Lcobucci\DependencyInjection\Builders;

use Lcobucci\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * The dependency injection builder for XML files
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class XmlBuilder extends ContainerBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function getLoader(SymfonyBuilder $container, array $path)
    {
        return new XmlFileLoader(
            $container,
            new FileLocator($path)
        );
    }
}
