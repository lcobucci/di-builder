<?php
namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\Handler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Definition of how the container builder should behave
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
interface Builder
{
    /**
     * Changes the generator to handle the files
     *
     * @param Generator $generator
     *
     * @return self
     */
    public function setGenerator(Generator $generator);

    /**
     * Add a file to be loaded
     *
     * @param string $file
     *
     * @return self
     */
    public function addFile($file);

    /**
     * Add a handler to be executed on container creation
     *
     * @param Handler $handler
     *
     * @return self
     */
    public function addHandler(Handler $handler);

    /**
     * Mark the container to be used as development mode
     *
     * @return self
     */
    public function useDevelopmentMode();

    /**
     * Configures the dump directory
     *
     * @param string $dir
     *
     * @return self
     */
    public function setDumpDir($dir);

    /**
     * Adds a default parameter
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function setParameter($name, $value);

    /**
     * Adds a path to load the files
     *
     * @param string $path
     *
     * @return self
     */
    public function addPath($path);

    /**
     * @param string $class
     *
     * @return self
     */
    public function setBaseClass($class);

    /**
     * Creates the container with the given configuration
     *
     * @return ContainerInterface
     */
    public function getContainer();
}
