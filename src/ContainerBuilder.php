<?php
namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Config\Handler;
use Lcobucci\DependencyInjection\Config\Handlers\ParameterBag;
use Lcobucci\DependencyInjection\Generators\Xml as XmlGenerator;
use Symfony\Component\Config\ConfigCache;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ContainerBuilder implements Builder
{
    /**
     * @var ContainerConfiguration
     */
    private $config;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ParameterBag
     */
    private $parameterBag;

    /**
     * @param ContainerConfiguration $config
     * @param Generator $generator
     * @param ParameterBag $parameterBag
     */
    public function __construct(
        ContainerConfiguration $config = null,
        Generator $generator = null,
        ParameterBag $parameterBag = null
    ) {
        $this->parameterBag = $parameterBag ?: new ParameterBag();
        $this->generator = $generator ?: new XmlGenerator();
        $this->config = $config ?: new ContainerConfiguration();

        $this->setDefaultConfiguration();
    }

    /**
     * Configures the default parameters and appends the handler
     */
    protected function setDefaultConfiguration()
    {
        $this->parameterBag->set('app.devmode', false);

        $this->config->addHandler($this->parameterBag);
    }

    /**
     * {@inheritdoc}
     */
    public function setGenerator(Generator $generator)
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFile($file)
    {
        $this->config->addFile($file);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addHandler(Handler $handler)
    {
        $this->config->addHandler($handler);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function useDevelopmentMode()
    {
        $this->parameterBag->set('app.devmode', true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDumpDir($dir)
    {
        $this->config->setDumpDir($dir);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameterBag->set($name, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPath($path)
    {
        $this->config->addPath($path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseClass($class)
    {
        $this->config->setBaseClass($class);

        return $this;
    }

    /**
     * @return ConfigCache
     */
    protected function createDumpCache()
    {
        return new ConfigCache(
            $this->config->getDumpFile(),
            $this->parameterBag->get('app.devmode')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer()
    {
        return $this->generator->generate(
            $this->config,
            $this->createDumpCache()
        );
    }
}
