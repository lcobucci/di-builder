<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\DumpXmlContainer;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Yaml;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * @coversDefaultClass \Lcobucci\DependencyInjection\Generator
 *
 * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
 * @uses \Lcobucci\DependencyInjection\Compiler
 * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
 * @uses \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer
 * @uses \Lcobucci\DependencyInjection\Generators\Yaml
 */
final class GeneratorTest extends TestCase
{
    use GeneratesDumpDirectory;

    private const DI_NAMESPACE = 'Lcobucci\\DiTests\\Generator';

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::initializeContainer
     */
    public function initializeContainerShouldAddTheConfigurationFileAsAResource(): void
    {
        $container = (new Yaml(__FILE__))->initializeContainer(
            new ContainerConfiguration(self::DI_NAMESPACE)
        );

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
            self::DI_NAMESPACE,
            [vfsStream::url('tests/services.yml')],
            [
                [new ParameterBag(['app.devmode' => true]), PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [
                    new DumpXmlContainer(new ConfigCache($this->dumpDirectory . '/dump.xml', true)),
                    PassConfig::TYPE_AFTER_REMOVING,
                    -255,
                ],
            ]
        );

        $dump = new ConfigCache($this->dumpDirectory . '/container.php', false);

        $container = (new Yaml(__FILE__))->generate($config, $dump);

        self::assertInstanceOf(stdClass::class, $container->get('testing'));
        self::assertFileExists($this->dumpDirectory . '/dump.xml');
    }
}
