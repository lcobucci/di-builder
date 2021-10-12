<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Config\Package;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function get_class;
use function iterator_to_array;

/**
 * @coversDefaultClass \Lcobucci\DependencyInjection\ContainerBuilder
 *
 * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
 * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
 * @uses \Lcobucci\DependencyInjection\Generator
 * @uses \Lcobucci\DependencyInjection\Generators\Xml
 */
final class ContainerBuilderTest extends TestCase
{
    /** @var Generator&MockObject */
    private Generator $generator;
    private ContainerConfiguration $config;
    private ParameterBag $parameterBag;

    /** @before */
    public function configureDependencies(): void
    {
        $this->generator    = $this->getMockForAbstractClass(Generator::class, [], '', false, true, true, ['generate']);
        $this->config       = new ContainerConfiguration('Me\\MyApp');
        $this->parameterBag = new ParameterBag();
    }

    /**
     * @test
     * @dataProvider supportedFormats
     *
     * @covers ::default
     * @covers ::xml
     * @covers ::delegating
     * @covers ::php
     * @covers ::yaml
     * @covers ::__construct
     * @covers ::setDefaultConfiguration
     */
    public function namedConstructorsShouldSimplifyTheObjectCreation(string $method, Generator $generator): void
    {
        $expected = new ContainerBuilder(
            new ContainerConfiguration('Lcobucci\\DependencyInjection'),
            $generator,
            new ParameterBag(),
        );

        // @phpstan-ignore-next-line
        self::assertEquals($expected, ContainerBuilder::$method(__FILE__, __NAMESPACE__));
    }

    /** @return iterable<string, array{string, Generator}> */
    public function supportedFormats(): iterable
    {
        yield 'default' => ['default', new Generators\Xml(__FILE__)];
        yield 'xml' => ['xml', new Generators\Xml(__FILE__)];
        yield 'yaml' => ['yaml', new Generators\Yaml(__FILE__)];
        yield 'php' => ['php', new Generators\Php(__FILE__)];
        yield 'delegating' => ['delegating', new Generators\Delegating(__FILE__)];
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::setDefaultConfiguration
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     */
    public function constructShouldReceiveTheDependenciesAsArguments(): void
    {
        new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertNotEmpty($this->config->getPassList());
        self::assertFalse($this->parameterBag->get('app.devmode'));
        self::assertTrue($this->parameterBag->get('container.dumper.inline_class_loader'));
        self::assertTrue($this->parameterBag->get('container.dumper.inline_factories'));
    }

    /**
     * @test
     *
     * @covers ::setGenerator
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function setGeneratorShouldChangeTheAttributeAndReturnSelf(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $generator = $this->getMockForAbstractClass(Generator::class, [], '', false);
        $expected  = new ContainerBuilder($this->config, $generator, $this->parameterBag);

        self::assertSame($builder, $builder->setGenerator($generator));
        self::assertEquals($expected, $builder);
    }

    /**
     * @test
     *
     * @covers ::addFile
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
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
     * @covers ::addPass
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function addPassShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass    = $this->createMock(CompilerPassInterface::class);

        self::assertSame($builder, $builder->addPass($pass));
        self::assertContains([$pass, PassConfig::TYPE_BEFORE_OPTIMIZATION, 0], $this->config->getPassList());
    }

    /**
     * @test
     *
     * @covers ::addDelayedPass
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function addDelayedPassShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass    = get_class($this->createMock(CompilerPassInterface::class));

        self::assertSame($builder, $builder->addDelayedPass($pass));
        self::assertContains([[$pass, []], PassConfig::TYPE_BEFORE_OPTIMIZATION, 0], $this->config->getPassList());
    }

    /**
     * @test
     *
     * @covers ::addPackage
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function addPackageShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $module  = $this->createMock(Package::class);

        self::assertSame($builder, $builder->addPackage($module::class));
        self::assertEquals([$module], $this->config->getPackages());
    }

    /**
     * @test
     *
     * @covers ::setDumpDir
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
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
     * @covers ::addPath
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
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
     * @covers ::setBaseClass
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
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
     * @covers ::useDevelopmentMode
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function useDevelopmentModeShouldChangeTheParameterAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->useDevelopmentMode());
        self::assertTrue($this->parameterBag->get('app.devmode'));
        self::assertFalse($this->parameterBag->get('container.dumper.inline_class_loader'));
        self::assertFalse($this->parameterBag->get('container.dumper.inline_factories'));
    }

    /**
     * @test
     *
     * @covers ::setParameter
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
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
     * @covers ::getContainer
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function getContainerShouldGenerateAndReturnTheContainer(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $container = $this->createMock(ContainerInterface::class);

        $this->generator->expects(self::once())
                        ->method('generate')
                        ->with($this->config, new ConfigCache($this->config->getDumpFile(), false))
                        ->willReturn($container);

        self::assertSame($container, $builder->getContainer());
    }

    /**
     * @test
     *
     * @covers ::getTestContainer
     *
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::__construct
     * @uses \Lcobucci\DependencyInjection\ContainerBuilder::setDefaultConfiguration
     */
    public function getTestContainerShouldGenerateAndReturnTheContainer(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $container = $this->createMock(ContainerInterface::class);

        $config = new ContainerConfiguration('Me\\MyApp\\Tests');
        $config->addPass($this->parameterBag);
        $config->addPass(new MakeServicesPublic(), PassConfig::TYPE_BEFORE_REMOVING);

        $cacheConfig = new ConfigCache($config->getDumpFile(), true);

        $this->generator->expects(self::once())
                        ->method('generate')
                        ->with($config, $cacheConfig)
                        ->willReturn($container);

        self::assertSame($container, $builder->getTestContainer());

        $compilerPasses = iterator_to_array($this->config->getPassList());
        self::assertCount(1, $compilerPasses);
        self::assertSame($this->parameterBag, $compilerPasses[0][0]);
    }
}
