<?php
namespace Lcobucci\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Container;

abstract class ContainerBuilder
{
    /**
     * Create a dump file (if needed) and load the container
     *
     * @param ContainerConfig $config
     *
     * @return SymfonyBuilder
     */
    public function getContainer(ContainerConfig $config)
    {
        if (!$config->getCache()->isFresh()) {
            $container = new SymfonyBuilder();
            $container->getParameterBag()->add($config->getDefaultParameters());

            $loader = $this->getLoader($container, $config->getPaths());
            $loader->load($config->getFile());

            $this->createDump($container, $config);
        }

        return $this->loadFromDump($config);
    }

    /**
     * Creates the dump file
     *
     * @param SymfonyBuilder $container
     * @param ContainerConfig $data
     */
    protected function createDump(SymfonyBuilder $container, ContainerConfig $config)
    {
        $data = array('class' => $config->getClassName());

        if ($baseClass = $config->getBaseClass()) {
            $data['base_class'] = $baseClass;
        }

        $dumper = new PhpDumper($container);

        $config->getCache()->write($dumper->dump($data), $container->getResources());
    }

    /**
     * Load the class from dump file
     *
     * @param ContainerConfig $config
     *
     * @return Container
     */
    protected function loadFromDump(ContainerConfig $config)
    {
        require_once $config->getCache()->getPath();
        $className = '\\' . $config->getClassName();

        return new $className();
    }

    /**
     * Returns the file loader
     *
     * @param SymfonyBuilder $container
     * @param array $path
     *
     * @return FileLoader
     */
    abstract protected function getLoader(SymfonyBuilder $container, array $path);
}
