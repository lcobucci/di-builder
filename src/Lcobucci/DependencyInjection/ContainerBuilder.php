<?php
namespace Lcobucci\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Container;

abstract class ContainerBuilder
{
    /**
     * Container base class (when you don't want to use the symfony's default class)
     *
     * @var string
     */
    protected $file;

    /**
     * @var ConfigCache
     */
    protected $cache;

    /**
     * @param string $file
     * @param string $cacheDir
     *
     * @return ConfigCache
     */
    public static function createCache($file, $cacheDir = null, $debug = false)
    {
        $cacheDir === null ? sys_get_temp_dir() : rtrim($cacheDir, '/');
        $cacheFile = 'Project' . md5($file) . 'ServiceContainer.php';

        return new ConfigCache($cacheDir . DIRECTORY_SEPARATOR . $cacheFile, $debug);
    }

    /**
     * Creates a new object
     *
     * @param string $file
     * @param ConfigCache $cache
     */
    public function __construct($file, ConfigCache $cache)
    {
        $this->file = $file;
        $this->cache = $cache;
    }

    /**
     * Create a dump file (if needed) and load the container
     *
     * @param array $defaultParameters
     * @param string $baseClass
     *
     * @return SymfonyBuilder
     */
    public function getContainer(
        array $defaultParameters = array(),
        $baseClass = null
    ) {
        $dumpClass = $this->createDumpClassName();

        if (!$this->cache->isFresh()) {
            $container = new SymfonyBuilder();
            $container->getParameterBag()->add($defaultParameters);

            $this->getLoader($container, array())->load($this->file);
            $this->createDump($container, $dumpClass, $baseClass);
        }

        return $this->loadFromDump($dumpClass);
    }

    /**
     * Retrieve the dump class name
     *
     * @param string $file
     *
     * @return string
     */
    protected function createDumpClassName()
    {
        return substr(basename((string) $this->cache), 0, -4);
    }

    /**
     * Creates the dump file
     *
     * @param SymfonyBuilder $container
     * @param string $className
     * @param string $baseClass
     */
    protected function createDump(SymfonyBuilder $container, $className, $baseClass)
    {
        $config = array('class' => $className);

        if ($baseClass !== null) {
            $config['base_class'] = $baseClass;
        }

        $dumper = new PhpDumper($container);
        $this->cache->write($dumper->dump($config), $container->getResources());
    }

    /**
     * Load the class from dump file
     *
     * @param string $className
     *
     * @return Container
     */
    protected function loadFromDump($className)
    {
        require_once (string) $this->cache;
        $className = '\\' . $className;

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
