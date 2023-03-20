<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use DirectoryIterator;
use Generator as PHPGenerator;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Yaml;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;

use function count;
use function file_get_contents;
use function file_put_contents;
use function iterator_to_array;
use function mkdir;

#[PHPUnit\CoversClass(Compiler::class)]
#[PHPUnit\UsesClass(ParameterBag::class)]
#[PHPUnit\UsesClass(ContainerConfiguration::class)]
#[PHPUnit\UsesClass(Generator::class)]
#[PHPUnit\UsesClass(Yaml::class)]
#[PHPUnit\UsesClass(MakeServicesPublic::class)]
final class CompilerTest extends TestCase
{
    private const EXPECTED_FILES = [
        'getTestingService.php',
        'AppContainer.php',
        'AppContainer.preload.php',
        'AppContainer.php.meta',
    ];

    private ContainerConfiguration $config;
    private ConfigCache $dump;
    private string $dumpDir;
    private ParameterBag $parameters;

    #[PHPUnit\Before]
    public function configureDependencies(): void
    {
        vfsStream::setup(
            'tests-compilation',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass } }'],
        );

        $this->parameters = new ParameterBag();
        $this->parameters->set('app.devmode', true);
        $this->parameters->set('container.dumper.inline_factories', false);
        $this->parameters->set('container.dumper.inline_class_loader', true);

        $this->dumpDir = $this->createDumpDirectory();
        $this->dump    = new ConfigCache($this->dumpDir . '/AppContainer.php', false);

        $this->config = new ContainerConfiguration(
            'Me\\CompilationTest',
            [vfsStream::url('tests-compilation/services.yml')],
            [
                [$this->parameters, PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [[MakeServicesPublic::class, []], PassConfig::TYPE_BEFORE_OPTIMIZATION],
            ],
        );

        $this->config->setDumpDir($this->dumpDir);
    }

    private function createDumpDirectory(): string
    {
        $dir = vfsStream::url('tests-compilation/tmp/me_myapp');
        mkdir($dir, 0777, true);

        return $dir;
    }

    #[PHPUnit\Test]
    public function compileShouldCreateMultipleFilesForDevelopmentMode(): void
    {
        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        $expectedFiles  = self::EXPECTED_FILES;
        $generatedFiles = iterator_to_array($this->getGeneratedFiles());

        self::assertCount(count($expectedFiles), $generatedFiles);

        foreach ($generatedFiles as $name => $file) {
            self::assertContains($name, $expectedFiles);
        }
    }

    #[PHPUnit\Test]
    public function compileShouldInlineFactoriesForProductionMode(): void
    {
        $this->parameters->set('app.devmode', false);
        $this->parameters->set('container.dumper.inline_factories', true);

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        $expectedFiles  = self::EXPECTED_FILES;
        $generatedFiles = iterator_to_array($this->getGeneratedFiles());

        self::assertCount(count($expectedFiles) - 1, $generatedFiles);

        foreach ($generatedFiles as $name => $file) {
            self::assertContains($name, $expectedFiles);
        }
    }

    #[PHPUnit\Test]
    public function compileShouldTrackChangesOnTheConfigurationFile(): void
    {
        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        self::assertStringContainsString(
            __FILE__,
            (string) file_get_contents($this->dumpDir . '/AppContainer.php.meta'),
        );
    }

    #[PHPUnit\Test]
    public function compileShouldAllowForLazyServices(): void
    {
        file_put_contents(
            vfsStream::url('tests-compilation/services.yml'),
            'services: { testing: { class: stdClass, lazy: true } }',
        );

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        $expectedFiles  = self::EXPECTED_FILES;
        $generatedFiles = iterator_to_array($this->getGeneratedFiles());

        self::assertCount(count($expectedFiles) + 1, $generatedFiles);
    }

    #[PHPUnit\Test]
    public function compilationShouldBeSkippedWhenFileAlreadyExists(): void
    {
        file_put_contents($this->dumpDir . '/AppContainer.php', 'testing');

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        $generatedFiles = iterator_to_array($this->getGeneratedFiles());

        self::assertCount(1, $generatedFiles);
    }

    #[PHPUnit\Test]
    public function compileShouldUseCustomContainerBuilders(): void
    {
        $compiler = new Compiler();
        $compiler->compile(
            $this->config,
            $this->dump,
            new Yaml(__FILE__, CustomContainerBuilderForTests::class),
        );

        $container = include $this->dumpDir . '/AppContainer.php';

        self::assertInstanceOf(Container::class, $container);
        self::assertTrue($container->hasParameter('built-with-very-special-builder'));
        self::assertTrue($container->getParameter('built-with-very-special-builder'));
    }

    /** @return PHPGenerator<string, SplFileInfo> */
    private function getGeneratedFiles(?string $dir = null): PHPGenerator
    {
        $dir ??= $this->dumpDir;

        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                yield from $this->getGeneratedFiles($fileInfo->getPathname());

                continue;
            }

            yield $fileInfo->getFilename() => $fileInfo;
        }
    }
}
