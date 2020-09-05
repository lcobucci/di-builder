<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class Generator
{
    private Compiler $compiler;
    private string $configurationFile;

    public function __construct(string $configurationFile)
    {
        $this->compiler          = new Compiler();
        $this->configurationFile = $configurationFile;
    }

    /**
     * Loads the container
     */
    public function generate(
        ContainerConfiguration $config,
        ConfigCache $dump
    ): ContainerInterface {
        $this->compiler->compile($config, $dump, $this);

        return $this->loadContainer($dump);
    }

    private function loadContainer(ConfigCache $dump): ContainerInterface
    {
        require_once $dump->getPath();
        $className = '\\' . ContainerConfiguration::CLASS_NAME;

        return new $className(); // @phpstan-ignore-line (class loaded dynamically and PHPStan can't evaluate that)
    }

    public function initializeContainer(ContainerConfiguration $config): SymfonyBuilder
    {
        $container = new SymfonyBuilder();
        $container->addResource(new FileResource($this->configurationFile));

        $loader = $this->getLoader($container, $config->getPaths());

        foreach ($config->getFiles() as $file) {
            $loader->load($file);
        }

        return $container;
    }

    /** @param string[] $paths */
    abstract public function getLoader(SymfonyBuilder $container, array $paths): LoaderInterface;
}
