<?php
namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
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
class Delegating extends Generator
{
    /**
     * {@inheritdoc}
     */
    protected function getLoader(SymfonyBuilder $container, array $paths)
    {
        $locator = new FileLocator($paths);

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
