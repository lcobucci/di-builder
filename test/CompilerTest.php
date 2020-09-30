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
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

use function bin2hex;
use function count;
use function exec;
use function file_get_contents;
use function file_put_contents;
use function iterator_to_array;
use function mkdir;
use function random_bytes;
use function realpath;

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

    /** @before */
    public function configureDependencies(): void
    {
        vfsStream::setup(
            'tests',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass } }']
        );

        $parameterBag = new ParameterBag();
        $parameterBag->set('app.devmode', true);
        $parameterBag->set('container.dumper.inline_factories', false);
        $parameterBag->set('container.dumper.inline_class_loader', true);

        $this->dumpDir = $this->createDumpDirectory();
        $this->dump    = new ConfigCache($this->dumpDir . '/AppContainer.php', false);

        $this->config = new ContainerConfiguration(
            'Me\\MyApp',
            [vfsStream::url('tests/services.yml')],
            [
                [$parameterBag, PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [[MakeServicesPublic::class, []], PassConfig::TYPE_BEFORE_OPTIMIZATION],
            ]
        );

        $this->config->setDumpDir($this->dumpDir);
    }

    private function createDumpDirectory(): string
    {
        $dir = __DIR__ . '/../tmp/' . bin2hex(random_bytes(5)) . '/me_myapp';
        mkdir($dir, 0777, true);

        return $dir;
    }

    /** @after */
    public function cleanUpDumpDirectory(): void
    {
        exec('rm -rf ' . realpath($this->dumpDir . '/../../'));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Generator
     * @uses \Lcobucci\DependencyInjection\Generators\Yaml
     * @uses \Lcobucci\DependencyInjection\Testing\MakeServicesPublic
     */
    public function compileShouldCreateMultipleFiles(): void
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

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Generator
     * @uses \Lcobucci\DependencyInjection\Generators\Yaml
     * @uses \Lcobucci\DependencyInjection\Testing\MakeServicesPublic
     */
    public function compileShouldTrackChangesOnTheConfigurationFile(): void
    {
        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        self::assertStringContainsString(
            __FILE__,
            (string) file_get_contents($this->dumpDir . '/AppContainer.php.meta')
        );
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Generator
     * @uses \Lcobucci\DependencyInjection\Generators\Yaml
     * @uses \Lcobucci\DependencyInjection\Testing\MakeServicesPublic
     */
    public function compileShouldAllowForLazyServices(): void
    {
        file_put_contents(
            vfsStream::url('tests/services.yml'),
            'services: { testing: { class: stdClass, lazy: true } }'
        );

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        $expectedFiles  = self::EXPECTED_FILES;
        $generatedFiles = iterator_to_array($this->getGeneratedFiles());

        self::assertCount(count($expectedFiles) + 1, $generatedFiles);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Generator
     */
    public function compilationShouldBeSkippedWhenFileAlreadyExists(): void
    {
        file_put_contents($this->dumpDir . '/AppContainer.php', 'testing');

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml(__FILE__));

        $generatedFiles = iterator_to_array($this->getGeneratedFiles());

        self::assertCount(1, $generatedFiles);
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
