<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Generator;
use Lcobucci\DependencyInjection\Builder;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Lcobucci\DependencyInjection\FileListProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

use function array_filter;
use function array_map;
use function ltrim;
use function rtrim;
use function str_replace;
use function strtolower;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

final class ContainerConfiguration
{
    public const CLASS_NAME = 'AppContainer';

    /** @var string[] */
    private array $files;

    /** @var list<array{0: CompilerPassInterface|array{0: class-string<CompilerPassInterface>, 1: mixed[]}, 1?: string, 2?: int}> */
    private array $passList;

    /** @var string[] */
    private array $paths;

    private ?string $baseClass = null;

    /** @var list<array{0: class-string<Package>, 1: mixed[]}> */
    private array $packages;

    /** @var Package[]|null */
    private ?array $initializedPackages = null;

    private string $dumpDir;
    private string $namespace;

    /**
     * phpcs:disable Generic.Files.LineLength
     *
     * @param string[]                                                                                                             $files
     * @param list<array{0: CompilerPassInterface|array{0: class-string<CompilerPassInterface>, 1: mixed[]}, 1?: string, 2?: int}> $passList
     * @param string[]                                                                                                             $paths
     * @param list<array{0: class-string<Package>, 1: mixed[]}>                                                                    $packages
     *
     * phpcs:enable Generic.Files.LineLength
     */
    public function __construct(
        string $namespace,
        array $files = [],
        array $passList = [],
        array $paths = [],
        array $packages = []
    ) {
        $this->namespace = $namespace;
        $this->files     = $files;
        $this->passList  = $passList;
        $this->paths     = $paths;
        $this->packages  = $packages;
        $this->dumpDir   = sys_get_temp_dir();
    }

    /** @return Package[] */
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
     * @param class-string<Package> $className
     * @param mixed[]               $constructArguments
     */
    public function addPackage(string $className, array $constructArguments = []): void
    {
        $this->packages[] = [$className, $constructArguments];
    }

    /** @return Generator<string> */
    public function getFiles(): Generator
    {
        foreach ($this->filterPackages(FileListProvider::class) as $package) {
            yield from $package->getFiles();
        }

        yield from $this->files;
    }

    /**
     * @template T
     *
     * @param class-string<T> $packageType
     *
     * @return array<T>
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

    /** @return Generator<array{0: CompilerPassInterface|array{0: class-string<CompilerPassInterface>, 1: mixed[]}, 1?: string, 2?: int}> */
    public function getPassList(): Generator
    {
        foreach ($this->filterPackages(CompilerPassListProvider::class) as $package) {
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
     * @param class-string<CompilerPassInterface> $className
     * @param mixed[]                             $constructArguments
     */
    public function addDelayedPass(
        string $className,
        array $constructArguments,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = Builder::DEFAULT_PRIORITY
    ): void {
        $this->passList[] = [[$className, $constructArguments], $type, $priority];
    }

    /** @return string[] */
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

    public function withSubNamespace(string $namespace): self
    {
        $config             = clone $this;
        $config->namespace .= '\\' . ltrim($namespace, '\\');

        return $config;
    }

    public function getDumpDir(): string
    {
        return $this->dumpDir;
    }

    public function setDumpDir(string $dumpDir): void
    {
        $this->dumpDir = rtrim($dumpDir, DIRECTORY_SEPARATOR);
    }

    public function getDumpFile(): string
    {
        return $this->dumpDir . DIRECTORY_SEPARATOR
             . strtolower(str_replace('\\', '_', $this->namespace)) . DIRECTORY_SEPARATOR
             . self::CLASS_NAME . '.php';
    }

    public function getClassName(): string
    {
        return $this->namespace . '\\' . self::CLASS_NAME;
    }

    /** @return array<string, mixed> */
    public function getDumpOptions(): array
    {
        $options = [
            'class' => self::CLASS_NAME,
            'namespace' => $this->namespace,
        ];

        if ($this->baseClass !== null) {
            $options['base_class'] = $this->baseClass;
        }

        $options['hot_path_tag'] = 'container.hot_path';

        return $options;
    }
}
