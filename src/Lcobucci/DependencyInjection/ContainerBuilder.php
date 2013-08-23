<?php
namespace Lcobucci\DependencyInjection;

/**
 * The dependency injection container builder
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
interface ContainerBuilder
{
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
    );

    /**
     * Creates a new object
     *
     * @param string $baseClass
     * @param string $cacheDirectory
     */
    public function __construct($baseClass = null, $cacheDirectory = null);

    /**
     * Create a dump file (if needed) and load the container
     *
     * @param string $file
     * @param array $path
     * @param array $defaultParameters
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainer($file, array $path = array(), array $defaultParameters = array());
}
