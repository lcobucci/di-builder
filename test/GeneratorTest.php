<?php
namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use org\bovigo\vfs\vfsStream;
/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var Compiler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->compiler = $this->getMock(Compiler::class);
        $this->generator = $this->getMockForAbstractClass(Generator::class, [$this->compiler]);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Generator::__construct
     */
    public function constructShouldConfigureTheCompiler()
    {
        $this->assertAttributeSame($this->compiler, 'compiler', $this->generator);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Generator::__construct
     */
    public function constructShouldCreateACompilerWhenNotInformed()
    {
        $generator = $this->getMockForAbstractClass(Generator::class);

        $this->assertAttributeInstanceOf(Compiler::class, 'compiler', $generator);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Generator::__construct
     * @covers Lcobucci\DependencyInjection\Generator::generate
     * @covers Lcobucci\DependencyInjection\Generator::loadContainer
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

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @param string $className
     *
     * @return ContainerConfiguration
     */
    private function createConfiguration($className)
    {
        $config = $this->getMock(ContainerConfiguration::class, [], [], '', false);

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
        $dump = $this->getMock(ConfigCache::class, [], [], '', false);

        $dump->expects($this->any())
             ->method('__toString')
             ->willReturn($file);

        return $dump;
    }
}
