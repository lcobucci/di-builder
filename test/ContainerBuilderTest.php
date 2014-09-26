<?php
namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Config\Handlers\ParameterBag;
use Lcobucci\DependencyInjection\Generators\Xml as XmlGenerator;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->generator = $this->getMockForAbstractClass(Generator::class, [], '', false, true, true, ['generate']);
        $this->config = $this->getMock(ContainerConfiguration::class, [], [], '', false);
        $this->parameterBag = $this->getMock(ParameterBag::class, [], [], '', false);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @covers Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     * @covers Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers Lcobucci\DependencyInjection\Config\ContainerConfiguration::addHandler
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__construct
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::set
     * @covers Lcobucci\DependencyInjection\Generators\Xml::__construct
     */
    public function constructShouldConfigureTheDefaultAttributes()
    {
        $builder = new ContainerBuilder();

        $this->assertAttributeInstanceOf(ContainerConfiguration::class, 'config', $builder);
        $this->assertAttributeInstanceOf(ParameterBag::class, 'parameterBag', $builder);
        $this->assertAttributeInstanceOf(XmlGenerator::class, 'generator', $builder);
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
                     ->method('addHandler')
                     ->with($this->parameterBag);

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertAttributeSame($this->config, 'config', $builder);
        $this->assertAttributeSame($this->parameterBag, 'parameterBag', $builder);
        $this->assertAttributeSame($this->generator, 'generator', $builder);
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
        $generator = $this->getMockForAbstractClass(Generator::class, [], '', false);

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->setGenerator($generator));
        $this->assertAttributeSame($generator, 'generator', $builder);
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
        $this->config->expects($this->once())
                     ->method('addFile')
                     ->with('test');

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->addFile('test'));
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
        $this->config->expects($this->once())
                     ->method('setDumpDir')
                     ->with('test');

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->setDumpDir('test'));
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
        $this->config->expects($this->once())
                     ->method('addPath')
                     ->with('test');

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->addPath('test'));
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
        $this->config->expects($this->once())
                     ->method('setBaseClass')
                     ->with('Test');

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->setBaseClass('Test'));
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
        $this->parameterBag->expects($this->at(0))
                           ->method('set')
                           ->with('app.devmode', false);

        $this->parameterBag->expects($this->at(1))
                           ->method('set')
                           ->with('app.devmode', true);

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->useDevelopmentMode());
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
        $this->parameterBag->expects($this->at(0))
                           ->method('set')
                           ->with('app.devmode', false);

        $this->parameterBag->expects($this->at(1))
                           ->method('set')
                           ->with('test', 1);

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($builder, $builder->setParameter('test', 1));
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
        $container = $this->getMock(ContainerInterface::class);

        $this->generator->expects($this->once())
                        ->method('generate')
                        ->with($this->config, $this->isInstanceOf(ConfigCache::class))
                        ->willReturn($container);

        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        $this->assertSame($container, $builder->getContainer());
    }
}
