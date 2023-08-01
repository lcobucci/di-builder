<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\DumpXmlContainer;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Yaml;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\UsesClass(ParameterBag::class)]
#[PHPUnit\UsesClass(ContainerConfiguration::class)]
#[PHPUnit\UsesClass(DumpXmlContainer::class)]
#[PHPUnit\UsesClass(Compiler::class)]
#[PHPUnit\UsesClass(Yaml::class)]
final class GeneratorTest extends TestCase
{
    use GeneratesDumpDirectory;

    private const DI_NAMESPACE = 'Lcobucci\\DiTests\\Generator';

    #[PHPUnit\Test]
    public function initializeContainerShouldAddTheConfigurationFileAsAResource(): void
    {
        $container = (new Yaml(__FILE__))->initializeContainer(
            new ContainerConfiguration(self::DI_NAMESPACE),
        );

        self::assertEquals([new FileResource(__FILE__)], $container->getResources());
    }

    #[PHPUnit\Test]
    public function initializeContainerCanOptionallyUseACustomClass(): void
    {
        $generator = new Yaml(__FILE__, CustomContainerBuilderForTests::class);

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
            self::DI_NAMESPACE,
            [vfsStream::url('tests-generation/services.yml')],
            [
                [new ParameterBag(['app.devmode' => true]), PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [
                    new DumpXmlContainer(new ConfigCache($this->dumpDirectory . '/dump.xml', true)),
                    PassConfig::TYPE_AFTER_REMOVING,
                    -255,
                ],
            ],
        );

        $dump = new ConfigCache($this->dumpDirectory . '/container.php', false);

        $container = (new Yaml(__FILE__))->generate($config, $dump);

        self::assertInstanceOf(stdClass::class, $container->get('testing'));
        self::assertFileExists($this->dumpDirectory . '/dump.xml');
    }
}
