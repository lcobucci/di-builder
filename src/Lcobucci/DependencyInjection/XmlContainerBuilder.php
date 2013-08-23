<?php
namespace Lcobucci\DependencyInjection;

use \Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use \Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use \Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use \Symfony\Component\Config\FileLocator;

/**
 * The dependency injection container builder
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class XmlContainerBuilder implements ContainerBuilder
{
    /**
     * Container base class (when you don't want to use the symfony's default class)
     *
     * @var string
     */
    protected $baseClass;

    /**
     * The directory for the PHP class to be saved
     *
     * @var string
     */
    protected $cacheDirectory;

    /**
     * Creates an instance of the builder and build the container for given file
     *
     * @param string $file
     * @param array $path
     * @param string $baseClass
     * @param string $cacheDirectory
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public static function build(
        $file,
        array $path = array(),
        $baseClass = null,
        $cacheDirectory = null
    ) {
        $builder = new static($baseClass, $cacheDirectory);

        return $builder->getContainer(realpath($file), $path);
    }

    /**
     * Creates a new object
     *
     * @param string $baseClass
     * @param string $cacheDirectory
     */
    public function __construct($baseClass = null, $cacheDirectory = null)
    {
        if ($baseClass !== null) {
            $this->baseClass = $baseClass;
        }

        if ($cacheDirectory === null) {
            $this->cacheDirectory = sys_get_temp_dir();

            return ;
        }

        $this->cacheDirectory = rtrim($cacheDirectory, '/');
    }

    /**
     * Create a dump file (if needed) and load the container
     *
     * @param string $file
     * @param array $path
     * @param array $defaultParameters
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer(
        $file,
        array $path = array(),
        array $defaultParameters = array()
    ) {
        $dumpClass = $this->createDumpClassName($file);

        if ($this->hasToCreateDumpClass($file, $dumpClass)) {
            $container = new SymfonyBuilder();

            foreach ($defaultParameters as $param => $value) {
                $container->setParameter($param, $value);
            }

            $this->getLoader($container, $path)->load($file);
            $this->createDump($container, $dumpClass);
        }

        return $this->loadFromDump($dumpClass);
    }

    /**
     * Retrieve the dump class name
     *
     * @param string $file
     * @return string
     */
    protected function createDumpClassName($file)
    {
        return 'Project' . md5($file) . 'ServiceContainer';
    }

    /**
     * Retrieve the dump file name
     *
     * @param string $className
     * @return string
     */
    protected function getDumpFileName($className)
    {
        return $this->cacheDirectory . '/' . $className . '.php';
    }

    /**
     * Verify if is needed to create a new dump
     *
     * @param string $file
     * @param string $className
     * @return boolean
     */
    protected function hasToCreateDumpClass($file, $className)
    {
        $dumpFile = $this->getDumpFileName($className);

        if (file_exists($dumpFile) && filemtime($dumpFile) >= filemtime($file)) {
            return false;
        }

        return true;
    }

    /**
     * Creates the dump file
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $className
     */
    protected function createDump(SymfonyBuilder $container, $className)
    {
        $config = array('class' => $className);

        if ($this->baseClass !== null) {
            $config['base_class'] = $this->baseClass;
        }

        $dumper = new PhpDumper($container);
        $dumpFile = $this->getDumpFileName($className);

        file_put_contents(
            $dumpFile,
            $dumper->dump($config)
        );

        chmod($dumpFile, 0777);
    }

    /**
     * Load the class from dump file
     *
     * @param string $className
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function loadFromDump($className)
    {
        require_once $this->getDumpFileName($className);
        $className = '\\' . $className;

        return new $className();
    }

    /**
     * Returns the file loader
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return \Symfony\Component\DependencyInjection\Loader\XmlFileLoader
     */
    protected function getLoader(SymfonyBuilder $container, array $path)
    {
        return new XmlFileLoader(
            $container,
            new FileLocator($path)
        );
    }
}
