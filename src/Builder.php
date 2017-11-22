<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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
     */
    public function setGenerator(Generator $generator): Builder;

    /**
     * Add a file to be loaded
     */
    public function addFile(string $file): Builder;

    /**
     * Add a compiler pass
     */
    public function addPass(
        CompilerPassInterface $pass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION
    ): Builder;

    public function addDelayedPass(
        string $className,
        array $constructArguments = [],
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION
    ): Builder;

    public function addPackage(string $className, array $constructArguments = []): Builder;

    /**
     * Mark the container to be used as development mode
     */
    public function useDevelopmentMode(): Builder;

    /**
     * Configures the dump directory
     */
    public function setDumpDir(string $dir): Builder;

    /**
     * Adds a default parameter
     */
    public function setParameter(string $name, $value): Builder;

    /**
     * Adds a path to load the files
     */
    public function addPath(string $path): Builder;

    /**
     * Configures the container's base class
     */
    public function setBaseClass(string $class): Builder;

    /**
     * Creates the container with the given configuration
     */
    public function getContainer(): ContainerInterface;

    /**
     * Creates a test container with the given configuration
     */
    public function getTestContainer(): ContainerInterface;
}
