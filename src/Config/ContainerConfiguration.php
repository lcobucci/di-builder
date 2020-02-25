<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Generator;
use Lcobucci\DependencyInjection\Builder;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Lcobucci\DependencyInjection\FileListProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use function array_column;
use function array_filter;
use function array_map;
use function array_merge;
use function assert;
use function implode;
use function md5;
use function rtrim;
use function sys_get_temp_dir;
use const DIRECTORY_SEPARATOR;

final class ContainerConfiguration
{
    /**
     * @var string[]
     */
    private array $files;

    /**
     * @var mixed[]
     */
    private array $passList;

    /**
     * @var string[]
     */
    private array $paths;

    private ?string $baseClass = null;

    /**
     * @var mixed[]
     */
    private array $packages;

    /**
     * @var Package[]|null
     */
    private ?array $initializedPackages = null;

    private string $dumpDir;

    /**
     * @param string[] $files
     * @param mixed[]  $passList
     * @param string[] $paths
     * @param mixed[]  $packages
     */
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
        if ($this->initializedPackages === null) {
            $this->initializedPackages = array_map(
                static function (array $data): Package {
                    [$package, $arguments] = $data;

                    return new $package(...$arguments);
                },
                $this->packages
            );
        }

        return $this->initializedPackages;
    }

    /**
     * @param mixed[] $constructArguments
     */
    public function addPackage(string $className, array $constructArguments = []): void
    {
        $this->packages[] = [$className, $constructArguments];
    }

    public function getFiles(): Generator
    {
        foreach ($this->filterPackages(FileListProvider::class) as $package) {
            assert($package instanceof FileListProvider);

            yield from $package->getFiles();
        }

        yield from $this->files;
    }

    /**
     * @return Package[]
     */
    private function filterPackages(string $packageType): array
    {
        return array_filter(
            $this->getPackages(),
            static function (Package $package) use ($packageType): bool {
                return $package instanceof $packageType;
            }
        );
    }

    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }

    public function getPassList(): Generator
    {
        foreach ($this->filterPackages(CompilerPassListProvider::class) as $package) {
            assert($package instanceof CompilerPassListProvider);

            yield from $package->getCompilerPasses();
        }

        yield from $this->passList;
    }

    public function addPass(
        CompilerPassInterface $pass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = Builder::DEFAULT_PRIORITY
    ): void {
        $this->passList[] = [$pass, $type, $priority];
    }

    /**
     * @param mixed[] $constructArguments
     */
    public function addDelayedPass(
        string $className,
        array $constructArguments,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = Builder::DEFAULT_PRIORITY
    ): void {
        $this->passList[] = [[$className, $constructArguments], $type, $priority];
    }

    /**
     * @return string[]
     */
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
        $relevantData = array_merge(
            $this->files,
            $this->paths,
            array_column($this->packages, 0)
        );

        return 'Container' . md5(implode(';', $relevantData));
    }

    public function getDumpFile(string $prefix = ''): string
    {
        return $this->dumpDir . DIRECTORY_SEPARATOR . $prefix . $this->getClassName() . '.php';
    }

    /**
     * @return array<string, mixed>
     */
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
