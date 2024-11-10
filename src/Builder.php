<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Definition of how the container builder should behave
 */
interface Builder
{
    public const DEFAULT_PRIORITY = 0;

    /**
     * Add a file to be loaded
     */
    public function addFile(string $file): Builder;

    /**
     * Add a compiler pass
     */
    public function addPass(
        CompilerPassInterface $pass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = self::DEFAULT_PRIORITY,
    ): Builder;

    /**
     * @param class-string<CompilerPassInterface> $className
     * @param mixed[]                             $constructArguments
     */
    public function addDelayedPass(
        string $className,
        array $constructArguments = [],
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = self::DEFAULT_PRIORITY,
    ): Builder;

    /**
     * @param class-string<Package> $className
     * @param mixed[]               $constructArguments
     */
    public function addPackage(string $className, array $constructArguments = []): Builder;

    /**
     * Configures the application profile name to support profile-specific services
     */
    public function setProfileName(string $profileName): Builder;

    /**
     * Configure the container to track file updates
     */
    public function enableDebugging(): Builder;

    /**
     * Configures the dump directory
     */
    public function setDumpDir(string $dir): Builder;

    /**
     * Adds a default parameter
     */
    public function setParameter(string $name, mixed $value): Builder;

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
