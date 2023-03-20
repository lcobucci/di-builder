<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\DumpXmlContainer;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\UsesClass(ParameterBag::class)]
#[PHPUnit\UsesClass(ContainerConfiguration::class)]
#[PHPUnit\UsesClass(DumpXmlContainer::class)]
#[PHPUnit\UsesClass(Compiler::class)]
final class GeneratorTest extends TestCase
{
    private Generator&MockObject $generator;

    #[PHPUnit\Before]
    public function configureDependencies(): void
    {
        $this->generator = $this->getMockForAbstractClass(Generator::class, [__FILE__]);
    }

    #[PHPUnit\Test]
    public function initializeContainerShouldAddTheConfigurationFileAsAResource(): void
    {
        $container = $this->generator->initializeContainer(new ContainerConfiguration('Me\\MyApp'));

        self::assertEquals([new FileResource(__FILE__)], $container->getResources());
    }

    #[PHPUnit\Test]
    public function initializeContainerCanOptionallyUseACustomClass(): void
    {
        $generator = $this->getMockForAbstractClass(
            Generator::class,
            [__FILE__, CustomContainerBuilderForTests::class],
        );

        self::assertInstanceOf(
            CustomContainerBuilderForTests::class,
            $generator->initializeContainer(new ContainerConfiguration('Me\\MyApp')),
        );
    }

    #[PHPUnit\Test]
    public function generateShouldCompileAndLoadTheContainer(): void
    {
        vfsStream::setup(
            'tests-generation',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass, public: true } }'],
        );

        $config = new ContainerConfiguration(
            'Me\\GenerationTest',
            [vfsStream::url('tests-generation/services.yml')],
            [
                [new ParameterBag(['app.devmode' => true]), PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [
                    new DumpXmlContainer(
                        new ConfigCache(vfsStream::url('tests-generation/dump.xml'), true),
                    ),
                    PassConfig::TYPE_AFTER_REMOVING,
                    -255,
                ],
            ],
        );

        $dump = new ConfigCache(vfsStream::url('tests-generation/container.php'), false);

        $this->generator->method('getLoader')->willReturnCallback(
            static function (SymfonyBuilder $container, array $paths): YamlFileLoader {
                return new YamlFileLoader(
                    $container,
                    new FileLocator($paths),
                );
            },
        );

        $container = $this->generator->generate($config, $dump);

        self::assertInstanceOf(stdClass::class, $container->get('testing'));
        self::assertFileExists(vfsStream::url('tests-generation/dump.xml'));
    }
}
