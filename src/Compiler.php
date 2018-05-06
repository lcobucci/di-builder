<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use function assert;
use function class_exists;
use function is_array;

final class Compiler
{
    private const DEFAULT_PASS_CONFIG = [null, null, 0];

    public function compile(
        ContainerConfiguration $config,
        ConfigCache $dump,
        Generator $generator
    ): void {
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
    ): void {
        $loader = $generator->getLoader($container, $config->getPaths());

        foreach ($config->getFiles() as $file) {
            $loader->load($file);
        }
    }

    private function configurePassList(
        SymfonyBuilder $container,
        ContainerConfiguration $config
    ): void {
        foreach ($config->getPassList() as $passConfig) {
            assert(is_array($passConfig));
            [$pass, $type, $priority] = $passConfig + self::DEFAULT_PASS_CONFIG;

            if (! $pass instanceof CompilerPassInterface) {
                [$className, $constructArguments] = $pass;

                $pass = new $className(...$constructArguments);
            }

            $container->addCompilerPass($pass, $type, $priority);
        }
    }

    private function updateDump(
        SymfonyBuilder $container,
        ContainerConfiguration $config,
        ConfigCache $dump
    ): void {
        $container->compile();

        $options          = $config->getDumpOptions();
        $options['file']  = $dump->getPath();
        $options['debug'] = $container->getParameter('app.devmode');

        $dump->write(
            $this->getDumper($container)->dump($options),
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
