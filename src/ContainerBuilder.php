<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Xml as XmlGenerator;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function assert;
use function is_bool;

final class ContainerBuilder implements Builder
{
    private ContainerConfiguration $config;
    private Generator $generator;
    private ParameterBag $parameterBag;

    public function __construct(
        ?ContainerConfiguration $config = null,
        ?Generator $generator = null,
        ?ParameterBag $parameterBag = null
    ) {
        $this->parameterBag = $parameterBag ?? new ParameterBag();
        $this->generator    = $generator ?? new XmlGenerator();
        $this->config       = $config ?? new ContainerConfiguration();

        $this->setDefaultConfiguration();
    }

    /**
     * Configures the default parameters and appends the handler
     */
    private function setDefaultConfiguration(): void
    {
        $this->parameterBag->set('app.devmode', false);
        $this->parameterBag->set('container.dumper.inline_factories', false);
        $this->parameterBag->set('container.dumper.inline_class_loader', true);

        $this->config->addPass($this->parameterBag);
    }

    public function setGenerator(Generator $generator): Builder
    {
        $this->generator = $generator;

        return $this;
    }

    public function addFile(string $file): Builder
    {
        $this->config->addFile($file);

        return $this;
    }

    public function addPass(
        CompilerPassInterface $pass,
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = self::DEFAULT_PRIORITY
    ): Builder {
        $this->config->addPass($pass, $type, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addDelayedPass(
        string $className,
        array $constructArguments = [],
        string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = self::DEFAULT_PRIORITY
    ): Builder {
        $this->config->addDelayedPass($className, $constructArguments, $type, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPackage(string $className, array $constructArguments = []): Builder
    {
        $this->config->addPackage($className, $constructArguments);

        return $this;
    }

    public function useDevelopmentMode(): Builder
    {
        $this->parameterBag->set('app.devmode', true);
        $this->parameterBag->set('container.dumper.inline_class_loader', false);

        return $this;
    }

    public function setDumpDir(string $dir): Builder
    {
        $this->config->setDumpDir($dir);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter(string $name, $value): Builder
    {
        $this->parameterBag->set($name, $value);

        return $this;
    }

    public function addPath(string $path): Builder
    {
        $this->config->addPath($path);

        return $this;
    }

    public function setBaseClass(string $class): Builder
    {
        $this->config->setBaseClass($class);

        return $this;
    }

    public function getContainer(): ContainerInterface
    {
        $devMode = $this->parameterBag->get('app.devmode');
        assert(is_bool($devMode));

        return $this->generator->generate(
            $this->config,
            new ConfigCache($this->config->getDumpFile(), $devMode)
        );
    }

    public function getTestContainer(): ContainerInterface
    {
        $config = clone $this->config;
        $config->addPass(new MakeServicesPublic(), PassConfig::TYPE_BEFORE_REMOVING);

        return $this->generator->generate(
            $config,
            new ConfigCache($config->getDumpFile('test_'), true)
        );
    }
}
