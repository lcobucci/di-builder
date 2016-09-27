<?php
namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Xml as XmlGenerator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var ContainerConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $parameterBag;

    /**
     * @before
     */
    public function configureDependencies()
    {
        $this->generator = $this->getMockForAbstractClass(Generator::class, [], '', false, true, true, ['generate']);
        $this->config = $this->createMock(ContainerConfiguration::class);
        $this->parameterBag = $this->createMock(ParameterBag::class);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPass
     * @covers Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     * @covers Lcobucci\DependencyInjection\Compiler\ParameterBag::set
     * @covers Lcobucci\DependencyInjection\Generators\Xml::__construct
     */
    public function constructShouldConfigureTheDefaultAttributes()
    {
        $builder = new ContainerBuilder();

        self::assertAttributeInstanceOf(ContainerConfiguration::class, 'config', $builder);
        self::assertAttributeInstanceOf(ParameterBag::class, 'parameterBag', $builder);
        self::assertAttributeInstanceOf(XmlGenerator::class, 'generator', $builder);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function constructShouldReceiveTheDependenciesAsArguments()
    {
        $this->parameterBag->expects($this->once())
                           ->method('set')
                           ->with('app.devmode', false);

        $this->config->expects($this->once())
                     ->method('addPass')
                     ->with($this->parameterBag);

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertAttributeSame($this->config, 'config', $builder);
        self::assertAttributeSame($this->parameterBag, 'parameterBag', $builder);
        self::assertAttributeSame($this->generator, 'generator', $builder);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setGenerator
     */
    public function setGeneratorShouldChangeTheAttributeAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $generator = $this->getMockForAbstractClass(Generator::class, [], '', false);

        self::assertSame($builder, $builder->setGenerator($generator));
        self::assertAttributeSame($generator, 'generator', $builder);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::addFile
     */
    public function addFileShouldAppendANewFileOnTheListAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->config->expects($this->once())
                     ->method('addFile')
                     ->with('test');

        self::assertSame($builder, $builder->addFile('test'));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::addPass
     */
    public function addPassShouldAppendANewHandlerOnTheListAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass = $this->createMock(CompilerPassInterface::class);

        $this->config->expects($this->once())
                     ->method('addPass')
                     ->with($pass, 'beforeOptimization');

        self::assertSame($builder, $builder->addPass($pass));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDumpDir
     */
    public function setDumpDirShouldChangeTheConfigureAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->config->expects($this->once())
                     ->method('setDumpDir')
                     ->with('test');

        self::assertSame($builder, $builder->setDumpDir('test'));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::addPath
     */
    public function addPathShouldAppendANewPathOnTheListAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->config->expects($this->once())
                     ->method('addPath')
                     ->with('test');

        self::assertSame($builder, $builder->addPath('test'));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setBaseClass
     */
    public function setBaseClassShouldConfigureTheBaseClassAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->config->expects($this->once())
                     ->method('setBaseClass')
                     ->with('Test');

        self::assertSame($builder, $builder->setBaseClass('Test'));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::useDevelopmentMode
     */
    public function useDevelopmentModeShouldChangeTheParameterAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->parameterBag->expects($this->once())
                           ->method('set')
                           ->with('app.devmode', true);

        self::assertSame($builder, $builder->useDevelopmentMode());
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setParameter
     */
    public function setParameterShouldConfigureTheParameterAndReturnSelf()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->parameterBag->expects($this->once())
                           ->method('set')
                           ->with('test', 1);

        self::assertSame($builder, $builder->setParameter('test', 1));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::createDumpCache
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::getContainer
     */
    public function getContainerShouldGenerateAndReturnTheContainer()
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $container = $this->createMock(ContainerInterface::class);

        $this->generator->expects($this->once())
                        ->method('generate')
                        ->with($this->config, $this->isInstanceOf(ConfigCache::class))
                        ->willReturn($container);

        self::assertSame($container, $builder->getContainer());
    }
}
