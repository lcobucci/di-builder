<?php
namespace Lcobucci\DependencyInjection\Config;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ContainerConfiguration
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
     * @var string
     */
    private $baseClass;

    /**
     * @var string
     */
    private $dumpDir;

    /**
     * @param array $files
     * @param array $passList
     * @param array $paths
     */
    public function __construct(
        array $files = [],
        array $passList = [],
        array $paths = []
    ) {
        $this->files = $files;
        $this->passList = $passList;
        $this->paths = $paths;
        $this->dumpDir = sys_get_temp_dir();
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param string $file
     */
    public function addFile($file)
    {
        $this->files[] = $file;
    }

    /**
     * @return array
     */
    public function getPassList()
    {
        return $this->passList;
    }

    /**
     * @param CompilerPassInterface $pass
     * @param string $type
     */
    public function addPass(CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION)
    {
        $this->passList[] = [$pass, $type];
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param string $path
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
    }

    /**
     * @return string
     */
    public function getBaseClass()
    {
        return $this->baseClass;
    }

    /**
     * @param string $baseClass
     */
    public function setBaseClass($baseClass)
    {
        $this->baseClass = $baseClass;
    }

    /**
     * @return string
     */
    public function getDumpDir()
    {
        return $this->dumpDir;
    }

    /**
     * @param string $dumpDir
     */
    public function setDumpDir($dumpDir)
    {
        $this->dumpDir = rtrim($dumpDir, DIRECTORY_SEPARATOR);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return 'Project' . md5(implode(';', array_merge($this->files, $this->paths))) . 'ServiceContainer';
    }

    /**
     * @return string
     */
    public function getDumpFile()
    {
        return $this->dumpDir . DIRECTORY_SEPARATOR . $this->getClassName() . '.php';
    }

    /**
     * @return array
     */
    public function getDumpOptions()
    {
        $options = ['class' => $this->getClassName()];

        if ($this->baseClass) {
            $options['base_class'] = $this->baseClass;
        }

        return $options;
    }
}
