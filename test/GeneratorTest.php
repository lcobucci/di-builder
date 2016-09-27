<?php
namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var Compiler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $compiler;

    /**
     * @before
     */
    public function configureDependencies()
    {
        $this->compiler = $this->createMock(Compiler::class);
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
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     * @covers \Lcobucci\DependencyInjection\Generator::generate
     * @covers \Lcobucci\DependencyInjection\Generator::loadContainer
     */
    public function generateShouldCompileAndLoadTheContainer()
    {
        vfsStream::setup(
            'tests',
            null,
            ['container.php' => '<?php class Test extends \Symfony\Component\DependencyInjection\Container {}']
        );

        $config = $this->createConfiguration('Test');
        $dump = $this->createDump(vfsStream::url('tests/container.php'));

        $container = $this->generator->generate($config, $dump);

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @param string $className
     *
     * @return ContainerConfiguration
     */
    private function createConfiguration($className)
    {
        $config = $this->createMock(ContainerConfiguration::class);

        $config->expects($this->any())
               ->method('getClassName')
               ->willReturn($className);

        return $config;
    }

    /**
     * @param string $file
     *
     * @return ConfigCache
     */
    private function createDump($file)
    {
        $dump = $this->createMock(ConfigCache::class);

        $dump->expects($this->any())
             ->method('getPath')
             ->willReturn($file);

        return $dump;
    }
}
