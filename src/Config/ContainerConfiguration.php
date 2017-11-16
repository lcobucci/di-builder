<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

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
     * @var string
     */
    private $dumpDir;

    public function __construct(
        array $files = [],
        array $passList = [],
        array $paths = []
    ) {
        $this->files    = $files;
        $this->passList = $passList;
        $this->paths    = $paths;
        $this->dumpDir  = sys_get_temp_dir();
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }

    public function getPassList(): array
    {
        return $this->passList;
    }

    public function addPass(
        CompilerPassInterface $pass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION
    ): void {
        $this->passList[] = [$pass, $type];
    }

    public function addDelayedPass(
        string $className,
        array $constructArguments,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION
    ): void {
        $this->passList[] = [[$className, $constructArguments], $type];
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
        return 'Project' . md5(implode(';', array_merge($this->files, $this->paths))) . 'ServiceContainer';
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

        return $options;
    }
}
