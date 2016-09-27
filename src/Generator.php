<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
abstract class Generator
{
    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @param Compiler $compiler
     */
    public function __construct(Compiler $compiler = null)
    {
        $this->compiler = $compiler ?: new Compiler();
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

    abstract public function getLoader(SymfonyBuilder $container, array $paths): LoaderInterface;
}
