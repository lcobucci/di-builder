<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\DumpXmlContainer;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @coversDefaultClass \Lcobucci\DependencyInjection\Generator
 *
 * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
 * @uses \Lcobucci\DependencyInjection\Compiler
 * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
 * @uses \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer
 */
final class GeneratorTest extends TestCase
{
    /** @var Generator&MockObject */
    private Generator $generator;

    /** @before */
    public function configureDependencies(): void
    {
        $this->generator = $this->getMockForAbstractClass(Generator::class, [__FILE__]);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::initializeContainer
     */
    public function initializeContainerShouldAddTheConfigurationFileAsAResource(): void
    {
        $container = $this->generator->initializeContainer(new ContainerConfiguration('Me\\MyApp'));

        self::assertEquals([new FileResource(__FILE__)], $container->getResources());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::generate
     * @covers ::initializeContainer
     * @covers ::loadContainer
     */
    public function generateShouldCompileAndLoadTheContainer(): void
    {
        vfsStream::setup(
            'tests',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass, public: true } }']
        );

        $config = new ContainerConfiguration(
            'Me\\MyApp',
            [vfsStream::url('tests/services.yml')],
            [
                [new ParameterBag(['app.devmode' => true]), PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [
                    new DumpXmlContainer(
                        new ConfigCache(vfsStream::url('tests/dump.xml'), true)
                    ),
                    PassConfig::TYPE_AFTER_REMOVING,
                    -255,
                ],
            ]
        );

        $dump = new ConfigCache(vfsStream::url('tests/container.php'), false);

        $this->generator->method('getLoader')->willReturnCallback(
            static function (SymfonyBuilder $container, array $paths): YamlFileLoader {
                return new YamlFileLoader(
                    $container,
                    new FileLocator($paths)
                );
            }
        );

        $container = $this->generator->generate($config, $dump);

        self::assertInstanceOf(stdClass::class, $container->get('testing'));
        self::assertFileExists(vfsStream::url('tests/dump.xml'));
    }
}
