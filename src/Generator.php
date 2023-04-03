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
    /** @var class-string<SymfonyBuilder> */
    private string $builderClass;

    /** @param class-string<SymfonyBuilder>|null $builderClass */
    public function __construct(
        private string $configurationFile,
        ?string $builderClass = null,
    ) {
        $this->compiler     = new Compiler();
        $this->builderClass = $builderClass ?? SymfonyBuilder::class;
    }

    /**
     * Loads the container
     */
    public function generate(
        ContainerConfiguration $config,
        ConfigCache $dump,
    ): ContainerInterface {
        $this->compiler->compile($config, $dump, $this);

        return $this->loadContainer($config, $dump);
    }

    private function loadContainer(ContainerConfiguration $config, ConfigCache $dump): ContainerInterface
    {
        require_once $dump->getPath();
        $className = $config->getClassName();

        return new $className();
    }

    public function initializeContainer(ContainerConfiguration $config): SymfonyBuilder
    {
        $container = new $this->builderClass();
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
