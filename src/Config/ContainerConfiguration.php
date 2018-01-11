<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Lcobucci\DependencyInjection\FileListProvider;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class ContainerConfiguration
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var array
     */
    private $passList;

    /**
     * @var array
     */
    private $paths;

    /**
     * @var string|null
     */
    private $baseClass;

    /**
     * @var array
     */
    private $packages;

    /**
     * @var Package[]
     */
    private $initializedPackages;

    /**
     * @var string
     */
    private $dumpDir;

    public function __construct(
        array $files = [],
        array $passList = [],
        array $paths = [],
        array $packages = []
    ) {
        $this->files    = $files;
        $this->passList = $passList;
        $this->paths    = $paths;
        $this->packages = $packages;
        $this->dumpDir  = sys_get_temp_dir();
    }

    /**
     * @return Package[]
     */
    public function getPackages(): array
    {
        if (! $this->initializedPackages) {
            $this->initializedPackages = array_map(
                function (array $data): Package {
                    [$package, $arguments] = $data;
                    return new $package(...$arguments);
                },
                $this->packages
            );
        }

        return $this->initializedPackages;
    }

    public function addPackage(string $className, array $constructArguments = []): void
    {
        $this->packages[] = [$className, $constructArguments];
    }

    public function getFiles(): \Generator
    {
        foreach ($this->getPackagesThatProvideFiles() as $module) {
            yield from $module->getFiles();
        }

        foreach ($this->files as $file) {
            yield $file;
        }
    }

    /**
     * @return FileListProvider[]
     */
    private function getPackagesThatProvideFiles(): array
    {
        return $this->filterModules(FileListProvider::class);
    }

    private function filterModules(string $moduleType): array
    {
        return array_filter(
            $this->getPackages(),
            function (Package $module) use ($moduleType): bool {
                return $module instanceof $moduleType;
            }
        );
    }

    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }

    public function getPassList(): \Generator
    {
        foreach ($this->getPackagesThatProvideCompilerPasses() as $module) {
            yield from $module->getCompilerPasses();
        }

        foreach ($this->passList as $compilerPass) {
            yield $compilerPass;
        }
    }

    /**
     * @return CompilerPassListProvider[]
     */
    private function getPackagesThatProvideCompilerPasses(): array
    {
        return $this->filterModules(CompilerPassListProvider::class);
    }

    public function addPass(
        CompilerPassInterface $pass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = 0
    ): void {
        $this->passList[] = [$pass, $type, $priority];
    }

    public function addDelayedPass(
        string $className,
        array $constructArguments,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = 0
    ): void {
        $this->passList[] = [[$className, $constructArguments], $type, $priority];
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function addPath(string $path): void
    {
        $this->paths[] = $path;
    }

    public function getBaseClass(): ?string
    {
        return $this->baseClass;
    }

    public function setBaseClass(string $baseClass): void
    {
        $this->baseClass = $baseClass;
    }

    public function getDumpDir(): string
    {
        return $this->dumpDir;
    }

    public function setDumpDir(string $dumpDir): void
    {
        $this->dumpDir = rtrim($dumpDir, DIRECTORY_SEPARATOR);
    }

    public function getClassName(): string
    {
        $hash = md5(implode(';', array_merge($this->files, $this->paths, array_column($this->packages, 0))));

        return 'Project' . $hash . 'ServiceContainer';
    }

    public function getDumpFile(string $prefix = ''): string
    {
        return $this->dumpDir . DIRECTORY_SEPARATOR . $prefix . $this->getClassName() . '.php';
    }

    public function getDumpOptions(): array
    {
        $options = ['class' => $this->getClassName()];

        if ($this->baseClass !== null) {
            $options['base_class'] = $this->baseClass;
        }

        $options['hot_path_tag'] = 'container.hot_path';

        return $options;
    }
}
