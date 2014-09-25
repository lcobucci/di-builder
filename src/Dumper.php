<?php
namespace Lcobucci\DependencyInjection;

use Symfony\Component\Config\ConfigCache;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class Dumper
{
    /**
     * @param ContainerConfiguration $config
     * @param ConfigCache $dump
     * @param Generator $generator
     */
    public function dump(
        ContainerConfiguration $config,
        ConfigCache $dump,
        Generator $generator
    ) {
        if ($dump->isFresh()) {
            return;
        }

        $container = $this->getBuilder();

        $this->loadFiles($container, $config, $generator);
        $this->processHandlers($container, $config);
        $this->updateDump($container, $config, $dump);
    }

    /**
     * @return SymfonyBuilder
     */
    protected function getBuilder()
    {
        return new SymfonyBuilder();
    }

    /**
     * @param SymfonyBuilder $container
     * @param ContainerConfiguration $config
     */
    private function loadFiles(
        SymfonyBuilder $container,
        ContainerConfiguration $config,
        Generator $generator
    ) {
        $loader = $generator->getLoader($container, $config->getPaths());

        foreach ($config->getFiles() as $file) {
            $loader->load($file);
        }
    }

    /**
     * @param SymfonyBuilder $container
     * @param ContainerConfiguration $config
     */
    private function processHandlers(
        SymfonyBuilder $container,
        ContainerConfiguration $config
    ) {
        foreach ($config->getHandlers() as $handler) {
            $handler($container);
        }
    }

    /**
     * @param SymfonyBuilder $container
     * @param ContainerConfiguration $config
     * @param ConfigCache $dump
     */
    private function updateDump(
        SymfonyBuilder $container,
        ContainerConfiguration $config,
        ConfigCache $dump
    ) {
        $dump->write(
            $this->getDumper($container)->dump($config->getDumpOptions()),
            $container->getResources()
        );
    }

    /**
     * @param SymfonyBuilder $container
     *
     * @return DumperInterface
     */
    protected function getDumper(SymfonyBuilder $container)
    {
        return new PhpDumper($container);
    }
}
