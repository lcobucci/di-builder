<?php
namespace Lcobucci\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Container;

abstract class Generator
{
    /**
     * Creates a dump file (if needed) and load the container
     *
     * @param ContainerConfig $config
     *
     * @return SymfonyBuilder
     */
    public function getContainer(ContainerConfig $config)
    {
        if (!$config->getCache()->isFresh()) {
            $this->build($config);
        }

        return $this->loadFromDump($config);
    }

    /**
     * @param ContainerConfig $config
     */
    private function build(ContainerConfig $config)
    {
        $container = $this->getBuilder();
        $container->getParameterBag()->add($config->getDefaultParameters());

        $loader = $this->getLoader($container, $config->getPaths());
        $loader->load($config->getFile());

        $this->createDump($container, $config);
    }

    /**
     * @return SymfonyBuilder
     */
    protected function getBuilder()
    {
        return new SymfonyBuilder();
    }

    /**
     * Creates the dump file
     *
     * @param SymfonyBuilder $container
     * @param ContainerConfig $data
     */
    private function createDump(SymfonyBuilder $container, ContainerConfig $config)
    {
        $data = array('class' => $config->getClassName());

        if ($baseClass = $config->getBaseClass()) {
            $data['base_class'] = $baseClass;
        }

        $config->getCache()->write(
            $this->getDumper($container)->dump($data),
            $container->getResources()
        );
    }

    /**
     * @param SymfonyBuilder $container
     *
     * @return PhpDumper
     */
    protected function getDumper(SymfonyBuilder $container)
    {
        return new PhpDumper($container);
    }

    /**
     * Load the class from dump file
     *
     * @param ContainerConfig $config
     *
     * @return Container
     */
    private function loadFromDump(ContainerConfig $config)
    {
        require_once (string) $config->getCache();
        $className = '\\' . $config->getClassName();

        return new $className();
    }

    /**
     * Returns the file loader
     *
     * @param SymfonyBuilder $container
     * @param array $paths
     *
     * @return FileLoader
     */
    abstract protected function getLoader(SymfonyBuilder $container, array $paths);
}
