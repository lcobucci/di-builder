<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Generator
{
    private Compiler $compiler;

    public function __construct()
    {
        $this->compiler = new Compiler();
    }

    /**
     * Loads the container
     */
    public function generate(
        ContainerConfiguration $config,
        ConfigCache $dump
    ): ContainerInterface {
        $this->compiler->compile($config, $dump, $this);

        return $this->loadContainer($config, $dump);
    }

    private function loadContainer(
        ContainerConfiguration $config,
        ConfigCache $dump
    ): ContainerInterface {
        require_once $dump->getPath();
        $className = '\\' . $config->getClassName();

        return new $className();
    }

    public function initializeContainer(ContainerConfiguration $config): SymfonyBuilder
    {
        $container = new SymfonyBuilder();

        $loader = $this->getLoader($container, $config->getPaths());

        foreach ($config->getFiles() as $file) {
            $loader->load($file);
        }

        return $container;
    }

    /**
     * @param string[] $paths
     */
    abstract public function getLoader(SymfonyBuilder $container, array $paths): LoaderInterface;
}
