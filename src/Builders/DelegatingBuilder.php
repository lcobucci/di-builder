<?php
namespace Lcobucci\DependencyInjection\Builders;

use Lcobucci\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * The dependency injection builder that allows XML, YAML and PHP files to be used as source
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class DelegatingBuilder extends ContainerBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function getLoader(SymfonyBuilder $container, array $path)
    {
        $locator = new FileLocator($path);

        return new DelegatingLoader(
            new LoaderResolver(
                [
                    new XmlFileLoader($container, $locator),
                    new YamlFileLoader($container, $locator),
                    new PhpFileLoader($container, $locator)
                ]
            )
        );
    }
}
