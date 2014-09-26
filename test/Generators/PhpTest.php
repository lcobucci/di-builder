<?php
namespace Lcobucci\DependencyInjection\Generators;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class PhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Generator::__construct
     * @covers Lcobucci\DependencyInjection\Generators\Php::getLoader
     */
    public function getLoaderShouldReturnAPhpLoader()
    {
        $container = $this->getMock(ContainerBuilder::class);
        $generator = new Php();

        $this->assertInstanceOf(PhpFileLoader::class, $generator->getLoader($container, []));
    }
}
