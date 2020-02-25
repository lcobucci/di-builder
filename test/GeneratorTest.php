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
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class GeneratorTest extends TestCase
{
    /**
     * @var Generator|MockObject
     */
    private Generator $generator;

    /**
     * @before
     */
    public function configureDependencies(): void
    {
        $this->generator = $this->getMockForAbstractClass(Generator::class);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     * @covers \Lcobucci\DependencyInjection\Generator::generate
     * @covers \Lcobucci\DependencyInjection\Generator::loadContainer
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     * @uses \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer
     */
    public function generateShouldCompileAndLoadTheContainer(): void
    {
        vfsStream::setup(
            'tests',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass, public: true } }']
        );

        $config = new ContainerConfiguration(
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
