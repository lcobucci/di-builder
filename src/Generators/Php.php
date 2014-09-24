<?php
namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * The dependency injection builder for PHP files
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class Php extends Generator
{
    /**
     * {@inheritdoc}
     */
    protected function getLoader(SymfonyBuilder $container, array $paths)
    {
        return new PhpFileLoader(
            $container,
            new FileLocator($paths)
        );
    }
}
