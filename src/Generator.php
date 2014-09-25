<?php
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
     * @var Dumper
     */
    private $dumper;

    /**
     * @param Dumper $dumper
     */
    public function __construct(Dumper $dumper = null)
    {
        $this->dumper = $dumper ?: new Dumper();
    }

    /**
     * Loads the container
     *
     * @param ContainerConfiguration $config
     * @param ConfigCache $dump
     *
     * @return ContainerInterface
     */
    public function generate(
        ContainerConfiguration $config,
        ConfigCache $dump
    ) {
        $this->dumper->dump($config, $dump, $this);

        return $this->loadContainer($config, $dump);
    }

    /**
     * @param ContainerConfiguration $config
     * @param ConfigCache $dump
     *
     * @return ContainerInterface
     */
    private function loadContainer(
        ContainerConfiguration $config,
        ConfigCache $dump
    ) {
        require_once (string) $dump;
        $className = '\\' . $config->getClassName();

        return new $className();
    }

    /**
     * @param SymfonyBuilder $container
     * @param array $paths
     *
     * @return LoaderInterface
     */
    abstract public function getLoader(SymfonyBuilder $container, array $paths);
}
