<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use org\bovigo\vfs\vfsStream;
use stdClass;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @before
     */
    public function configureDependencies()
    {
        $this->compiler = new Compiler();
        $this->generator = $this->getMockForAbstractClass(Generator::class, [$this->compiler]);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     */
    public function constructShouldConfigureTheCompiler()
    {
        self::assertAttributeSame($this->compiler, 'compiler', $this->generator);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     */
    public function constructShouldCreateACompilerWhenNotInformed()
    {
        $generator = $this->getMockForAbstractClass(Generator::class);

        self::assertAttributeInstanceOf(Compiler::class, 'compiler', $generator);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::generate
     * @covers \Lcobucci\DependencyInjection\Generator::loadContainer
     *
     * @uses \Lcobucci\DependencyInjection\Generator::__construct
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration
     * @uses \Lcobucci\DependencyInjection\Compiler
     */
    public function generateShouldCompileAndLoadTheContainer()
    {
        vfsStream::setup(
            'tests',
            null,
            ['services.yml' => 'services: { testing: { class: stdClass } }']
        );

        $config = new ContainerConfiguration([vfsStream::url('tests/services.yml')]);
        $dump = new ConfigCache(vfsStream::url('tests/container.php'), false);

        $this->generator->method('getLoader')->willReturnCallback(
            function (SymfonyBuilder $container, array $paths) {
                return new YamlFileLoader(
                    $container,
                    new FileLocator($paths)
                );
            }
        );

        $container = $this->generator->generate($config, $dump);

        self::assertInstanceOf(ContainerInterface::class, $container);
        self::assertInstanceOf(stdClass::class, $container->get('testing'));
    }
}
