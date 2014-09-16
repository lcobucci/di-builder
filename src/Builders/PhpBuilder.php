<?php
namespace Lcobucci\DependencyInjection\Builders;

use Lcobucci\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * The dependency injection builder for PHP files
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class PhpBuilder extends ContainerBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function getLoader(SymfonyBuilder $container, array $path)
    {
        return new PhpFileLoader(
            $container,
            new FileLocator($path)
        );
    }
}
