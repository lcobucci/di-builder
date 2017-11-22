<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Config\Package;
use Lcobucci\DependencyInjection\Generators\Xml as XmlGenerator;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class ContainerBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Generator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $generator;

    /**
     * @var ContainerConfiguration
     */
    private $config;

    /**
     * @var ParameterBag
     */
    private $parameterBag;

    /**
     * @before
     */
    public function configureDependencies(): void
    {
        $this->generator    = $this->getMockForAbstractClass(Generator::class, [], '', false, true, true, ['generate']);
        $this->config       = new ContainerConfiguration();
        $this->parameterBag = new ParameterBag();
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     * @uses \Lcobucci\DependencyInjection\Generator
     * @uses \Lcobucci\DependencyInjection\Generators\Xml
     */
    public function constructShouldConfigureTheDefaultAttributes(): void
    {
        $builder = new ContainerBuilder();

        self::assertAttributeInstanceOf(ContainerConfiguration::class, 'config', $builder);
        self::assertAttributeInstanceOf(ParameterBag::class, 'parameterBag', $builder);
        self::assertAttributeInstanceOf(XmlGenerator::class, 'generator', $builder);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function constructShouldReceiveTheDependenciesAsArguments(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertAttributeSame($this->config, 'config', $builder);
        self::assertAttributeSame($this->parameterBag, 'parameterBag', $builder);
        self::assertAttributeSame($this->generator, 'generator', $builder);
        self::assertNotEmpty($this->config->getPassList());
        self::assertFalse($this->parameterBag->get('app.devmode'));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::setGenerator
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function setGeneratorShouldChangeTheAttributeAndReturnSelf(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $generator = $this->getMockForAbstractClass(Generator::class, [], '', false);

        self::assertSame($builder, $builder->setGenerator($generator));
        self::assertAttributeSame($generator, 'generator', $builder);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::addFile
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function addFileShouldAppendANewFileOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->addFile('test'));
        self::assertContains('test', $this->config->getFiles());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::addPass
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function addPassShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass    = $this->createMock(CompilerPassInterface::class);

        self::assertSame($builder, $builder->addPass($pass));
        self::assertContains([$pass, PassConfig::TYPE_BEFORE_OPTIMIZATION], $this->config->getPassList());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::addDelayedPass
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function addDelayedPassShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass    = get_class($this->createMock(CompilerPassInterface::class));

        self::assertSame($builder, $builder->addDelayedPass($pass));
        self::assertContains([[$pass, []], PassConfig::TYPE_BEFORE_OPTIMIZATION], $this->config->getPassList());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::addPackage
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function addPackageShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $module  = $this->createMock(Package::class);

        self::assertSame($builder, $builder->addPackage(get_class($module)));
        self::assertEquals([$module], $this->config->getPackages());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::setDumpDir
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function setDumpDirShouldChangeTheConfigureAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->setDumpDir('test'));
        self::assertEquals('test', $this->config->getDumpDir());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::addPath
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function addPathShouldAppendANewPathOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->addPath('test'));
        self::assertContains('test', $this->config->getPaths());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::setBaseClass
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function setBaseClassShouldConfigureTheBaseClassAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->setBaseClass('Test'));
        self::assertEquals('Test', $this->config->getBaseClass());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::useDevelopmentMode
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function useDevelopmentModeShouldChangeTheParameterAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->useDevelopmentMode());
        self::assertTrue($this->parameterBag->get('app.devmode'));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::setParameter
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function setParameterShouldConfigureTheParameterAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->setParameter('test', 1));
        self::assertEquals(1, $this->parameterBag->get('test'));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::getContainer
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function getContainerShouldGenerateAndReturnTheContainer(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $container = $this->createMock(ContainerInterface::class);

        $this->generator->expects($this->once())
                        ->method('generate')
                        ->with($this->config, $this->equalTo(new ConfigCache($this->config->getDumpFile(), false)))
                        ->willReturn($container);

        self::assertSame($container, $builder->getContainer());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\ContainerBuilder::getTestContainer
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function getTestContainerShouldGenerateAndReturnTheContainer(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $container = $this->createMock(ContainerInterface::class);

        $config = new ContainerConfiguration();
        $config->addPass($this->parameterBag);
        $config->addPass(new MakeServicesPublic());

        $cacheConfig = new ConfigCache($config->getDumpFile('test_'), true);

        $this->generator->expects($this->once())
                        ->method('generate')
                        ->with($this->equalTo($config), $this->equalTo($cacheConfig))
                        ->willReturn($container);

        self::assertSame($container, $builder->getTestContainer());
    }
}
