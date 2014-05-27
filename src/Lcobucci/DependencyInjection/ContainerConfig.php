<?php
namespace Lcobucci\DependencyInjection;

use Symfony\Component\Config\ConfigCache;
use InvalidArgumentException;

/**
 * @author Luís Otávio Cobucci Oblonczyk <luis@meritt.com.br>
 */
class ContainerConfig
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $pathId;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var array
     */
    private $defaultParameters;

    /**
     * @var string
     */
    private $baseClass;

    /**
     * @var array
     */
    private $paths;

    /**
     * @var ConfigCache
     */
    private $cache;

    /**
     * @param string $file
     * @param string $cacheDir
     * @param array $defaultParameters
     * @param string $baseClass
     * @param string $devMode
     * @param array $paths
     */
    public function __construct(
        $file,
        $cacheDir = null,
        array $defaultParameters = array(),
        $baseClass = null,
        $devMode = true,
        array $paths = array()
    ) {
        $defaultParameters['app.devmode'] = $devMode;

        $this->setFile($file);
        $this->setCacheDir($cacheDir);
        $this->defaultParameters = $defaultParameters;
        $this->baseClass = $baseClass;
        $this->paths = $paths;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     *
     * @throws InvalidArgumentException
     */
    protected function setFile($file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException('You must inform the valid path to the configuration file');
        }

        $this->pathId = md5(realpath($file));
        $this->file = $file;
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return $this->defaultParameters;
    }

    /**
     * @return string
     */
    public function getBaseClass()
    {
        return $this->baseClass;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return 'Project' . $this->pathId . 'ServiceContainer';
    }

    /**
     * @return ConfigCache
     */
    public function getCache()
    {
        if ($this->cache === null) {
            $this->cache = new ConfigCache($this->getCachePath(), $this->defaultParameters['app.devmode']);
        }

        return $this->cache;
    }

    /**
     * @param string $cacheDir
     */
    protected function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir === null ? sys_get_temp_dir() : rtrim($cacheDir, '/');
    }

    /**
     * @return string
     */
    protected function getCachePath()
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . $this->getClassName() . '.php';
    }
}
