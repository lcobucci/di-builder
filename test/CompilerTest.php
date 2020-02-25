<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Generator as PHPGenerator;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Yaml;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use function assert;
use function count;
use function file_put_contents;
use function iterator_to_array;
use function umask;

final class CompilerTest extends TestCase
{
    private const EXPECTED_FILES = [
        'getTestingService.php',
        'container.php',
        'container.php.meta',
    ];

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var ContainerConfiguration
     */
    private $config;

    /**
     * @var ConfigCache
     */
    private $dump;

    /**
     * @before
     */
    public function configureDependencies(): void
    {
        $this->root = vfsStream::setup(
            'tests',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass } }']
        );

        $parameterBag = new ParameterBag();
        $parameterBag->set('app.devmode', true);
        $parameterBag->set('container.dumper.inline_factories', false);
        $parameterBag->set('container.dumper.inline_class_loader', true);

        $this->config = new ContainerConfiguration(
            [vfsStream::url('tests/services.yml')],
            [
                [$parameterBag, PassConfig::TYPE_BEFORE_OPTIMIZATION],
                [[MakeServicesPublic::class, []], PassConfig::TYPE_BEFORE_OPTIMIZATION],
            ]
        );

        $this->dump = new ConfigCache(vfsStream::url('tests/container.php'), false);
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
        $compiler->compile($this->config, $this->dump, new Yaml());

        $expectedFiles   = self::EXPECTED_FILES;
        $expectedFiles[] = $this->config->getClassName() . '.php';

        $expectedPermissions = 0666 & ~umask();
        $generatedFiles      = iterator_to_array($this->getGeneratedFiles($this->root));

        self::assertCount(count($expectedFiles), $generatedFiles);

        foreach ($generatedFiles as $name => $file) {
            assert($file instanceof vfsStreamFile);

            self::assertContains($name, $expectedFiles);
            self::assertSame($expectedPermissions, $file->getPermissions());
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
    public function compileShouldAllowForLazyServices(): void
    {
        file_put_contents(
            vfsStream::url('tests/services.yml'),
            'services: { testing: { class: stdClass, lazy: true } }'
        );

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml());

        $expectedFiles   = self::EXPECTED_FILES;
        $expectedFiles[] = $this->config->getClassName() . '.php';

        self::assertCount(count($expectedFiles) + 1, iterator_to_array($this->getGeneratedFiles($this->root)));
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
        file_put_contents(vfsStream::url('tests/container.php'), 'testing');

        $compiler = new Compiler();
        $compiler->compile($this->config, $this->dump, new Yaml());

        $generatedFiles = iterator_to_array($this->getGeneratedFiles($this->root));

        self::assertCount(1, $generatedFiles);
    }

    private function getGeneratedFiles(vfsStreamDirectory $directory): PHPGenerator
    {
        foreach ($directory->getChildren() as $child) {
            if ($child instanceof vfsStreamDirectory) {
                yield from $this->getGeneratedFiles($child);
                continue;
            }

            if ($child->getName() !== 'services.yml') {
                yield $child->getName() => $child;
            }
        }
    }
}
