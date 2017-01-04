<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class Compiler
{
    /**
     * @param ContainerConfiguration $config
     * @param ConfigCache $dump
     * @param Generator $generator
     */
    public function compile(
        ContainerConfiguration $config,
        ConfigCache $dump,
        Generator $generator
    ) {
        if ($dump->isFresh()) {
            return;
        }

        $container = new SymfonyBuilder();

        $this->loadFiles($container, $config, $generator);
        $this->configurePassList($container, $config);
        $this->updateDump($container, $config, $dump);
    }

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

    private function configurePassList(
        SymfonyBuilder $container,
        ContainerConfiguration $config
    ) {
        foreach ($config->getPassList() as $pass) {
            $container->addCompilerPass(...$pass);
        }
    }

    private function updateDump(
        SymfonyBuilder $container,
        ContainerConfiguration $config,
        ConfigCache $dump
    ) {
        $container->compile();

        $dump->write(
            $this->getDumper($container)->dump($config->getDumpOptions()),
            $container->getResources()
        );
    }

    private function getDumper(SymfonyBuilder $container): DumperInterface
    {
        $dumper = new PhpDumper($container);

        if (class_exists(ProxyDumper::class)) {
            $dumper->setProxyDumper(new ProxyDumper());
        }

        return $dumper;
    }
}
