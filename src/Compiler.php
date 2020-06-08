<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use RuntimeException;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Filesystem\Filesystem;

use function array_pop;
use function assert;
use function class_exists;
use function dirname;
use function is_array;
use function is_int;
use function is_string;

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
            [$pass, $type, $priority] = $passConfig + self::DEFAULT_PASS_CONFIG;
            assert(is_string($type));
            assert(is_int($priority));

            if (! $pass instanceof CompilerPassInterface) {
                [$className, $constructArguments] = $pass;

                $pass = new $className(...$constructArguments);
                assert($pass instanceof CompilerPassInterface);
            }

            $container->addCompilerPass($pass, $type, $priority);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function updateDump(
        SymfonyBuilder $container,
        ContainerConfiguration $config,
        ConfigCache $dump
    ): void {
        $container->compile();

        $this->writeToFiles(
            $this->getContainerContent($container, $config, $dump),
            dirname($dump->getPath()) . '/',
            $dump,
            $container
        );
    }

    /**
     * @return string[]
     */
    private function getContainerContent(
        SymfonyBuilder $container,
        ContainerConfiguration $config,
        ConfigCache $dump
    ): array {
        $options             = $config->getDumpOptions();
        $options['file']     = $dump->getPath();
        $options['debug']    = $container->getParameter('app.devmode');
        $options['as_files'] = true;

        $content = $this->getDumper($container)->dump($options);
        assert(is_array($content));

        return $content;
    }

    /**
     * @param string[] $content
     *
     * @throws RuntimeException
     */
    private function writeToFiles(
        array $content,
        string $baseDir,
        ConfigCache $dump,
        SymfonyBuilder $container
    ): void {
        $rootCode = array_pop($content);
        assert(is_string($rootCode));

        $filesystem = new Filesystem();

        foreach ($content as $file => $code) {
            $filesystem->dumpFile($baseDir . $file, $code);
        }

        $dump->write($rootCode, $container->getResources());
    }

    private function getDumper(SymfonyBuilder $container): PhpDumper
    {
        $dumper = new PhpDumper($container);

        if (class_exists(ProxyDumper::class)) {
            $dumper->setProxyDumper(new ProxyDumper());
        }

        return $dumper;
    }
}
