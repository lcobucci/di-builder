<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Config\Package;
use Lcobucci\DependencyInjection\Generators\Xml;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function get_class;
use function iterator_to_array;

#[PHPUnit\CoversClass(ContainerBuilder::class)]
#[PHPUnit\UsesClass(ParameterBag::class)]
#[PHPUnit\UsesClass(ContainerConfiguration::class)]
#[PHPUnit\UsesClass(Generator::class)]
#[PHPUnit\UsesClass(Xml::class)]
final class ContainerBuilderTest extends TestCase
{
    /** @var Generator&MockObject */
    private Generator $generator;
    private ContainerConfiguration $config;
    private ParameterBag $parameterBag;

    #[PHPUnit\Before]
    public function configureDependencies(): void
    {
        $this->generator    = $this->getMockForAbstractClass(Generator::class, [], '', false, true, true, ['generate']);
        $this->config       = new ContainerConfiguration('Me\\MyApp');
        $this->parameterBag = new ParameterBag();
    }

    #[PHPUnit\Test]
    #[PHPUnit\DataProvider('supportedFormats')]
    public function namedConstructorsShouldSimplifyTheObjectCreation(
        string $method,
        Generator $generator,
        ?string $builderClass = null,
    ): void {
        $expected = new ContainerBuilder(
            new ContainerConfiguration('Lcobucci\\DependencyInjection'),
            $generator,
            new ParameterBag(),
        );

        // @phpstan-ignore-next-line
        self::assertEquals($expected, ContainerBuilder::$method(__FILE__, __NAMESPACE__, $builderClass));
    }

    /** @return iterable<string, array{string, Generator, 2?: class-string<\Symfony\Component\DependencyInjection\ContainerBuilder>}> */
    public static function supportedFormats(): iterable
    {
        yield 'default' => ['default', new Generators\Xml(__FILE__)];
        yield 'xml' => ['xml', new Generators\Xml(__FILE__)];
        yield 'yaml' => ['yaml', new Generators\Yaml(__FILE__)];
        yield 'php' => ['php', new Generators\Php(__FILE__)];
        yield 'delegating' => ['delegating', new Generators\Delegating(__FILE__)];

        yield 'xml with custom builder' => [
            'xml',
            new Generators\Xml(__FILE__, CustomContainerBuilderForTests::class),
            CustomContainerBuilderForTests::class,
        ];

        yield 'yaml with custom builder' => [
            'yaml',
            new Generators\Yaml(__FILE__, CustomContainerBuilderForTests::class),
            CustomContainerBuilderForTests::class,
        ];

        yield 'php with custom builder' => [
            'php',
            new Generators\Php(__FILE__, CustomContainerBuilderForTests::class),
            CustomContainerBuilderForTests::class,
        ];

        yield 'delegating with custom builder' => [
            'delegating',
            new Generators\Delegating(__FILE__, CustomContainerBuilderForTests::class),
            CustomContainerBuilderForTests::class,
        ];
    }

    #[PHPUnit\Test]
    public function constructShouldReceiveTheDependenciesAsArguments(): void
    {
        new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertNotSame([], iterator_to_array($this->config->getPassList()));
        self::assertFalse($this->parameterBag->get('app.devmode'));
        self::assertTrue($this->parameterBag->get('container.dumper.inline_class_loader'));
        self::assertTrue($this->parameterBag->get('container.dumper.inline_factories'));
    }

    #[PHPUnit\Test]
    public function setGeneratorShouldChangeTheAttributeAndReturnSelf(): void
    {
        $builder   = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $generator = $this->getMockForAbstractClass(Generator::class, [], '', false);
        $expected  = new ContainerBuilder($this->config, $generator, $this->parameterBag);

        // @phpstan-ignore-next-line method is deprecated and will be removed in the next major version
        self::assertSame($builder, $builder->setGenerator($generator));
        self::assertEquals($expected, $builder);
    }

    #[PHPUnit\Test]
    public function addFileShouldAppendANewFileOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->addFile('test'));
        self::assertContains('test', $this->config->getFiles());
    }

    #[PHPUnit\Test]
    public function addPassShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass    = $this->createMock(CompilerPassInterface::class);

        self::assertSame($builder, $builder->addPass($pass));
        self::assertContains([$pass, PassConfig::TYPE_BEFORE_OPTIMIZATION, 0], $this->config->getPassList());
    }

    #[PHPUnit\Test]
    public function addDelayedPassShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $pass    = get_class($this->createMock(CompilerPassInterface::class));

        self::assertSame($builder, $builder->addDelayedPass($pass));
        self::assertContains([[$pass, []], PassConfig::TYPE_BEFORE_OPTIMIZATION, 0], $this->config->getPassList());
    }

    #[PHPUnit\Test]
    public function addPackageShouldAppendANewHandlerOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);
        $module  = $this->createMock(Package::class);

        self::assertSame($builder, $builder->addPackage($module::class));
        self::assertEquals([$module], $this->config->getPackages());
    }

    #[PHPUnit\Test]
    public function setDumpDirShouldChangeTheConfigureAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->setDumpDir('test'));
        self::assertEquals('test', $this->config->getDumpDir());
    }

    #[PHPUnit\Test]
    public function addPathShouldAppendANewPathOnTheListAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->addPath('test'));
        self::assertContains('test', $this->config->getPaths());
    }

    #[PHPUnit\Test]
    public function setBaseClassShouldConfigureTheBaseClassAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->setBaseClass('Test'));
        self::assertEquals('\\Test', $this->config->getBaseClass());
    }

    #[PHPUnit\Test]
    public function useDevelopmentModeShouldChangeTheParameterAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->useDevelopmentMode());
        self::assertTrue($this->parameterBag->get('app.devmode'));
        self::assertFalse($this->parameterBag->get('container.dumper.inline_class_loader'));
        self::assertFalse($this->parameterBag->get('container.dumper.inline_factories'));
    }

    #[PHPUnit\Test]
    public function setParameterShouldConfigureTheParameterAndReturnSelf(): void
    {
        $builder = new ContainerBuilder($this->config, $this->generator, $this->parameterBag);

        self::assertSame($builder, $builder->setParameter('test', 1));
        self::assertEquals(1, $this->parameterBag->get('test'));
    }

    #[PHPUnit\Test]
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

    #[PHPUnit\Test]
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
