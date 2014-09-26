<?php
namespace Lcobucci\DependencyInjection\Generators;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class DelegatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Generator::__construct
     * @covers Lcobucci\DependencyInjection\Generators\Delegating::getLoader
     */
    public function getLoaderShouldReturnADelegatingLoaderWithTheOtherLoaders()
    {
        $container = $this->getMock(ContainerBuilder::class);
        $generator = new Delegating();

        $loader = $generator->getLoader($container, []);

        $this->assertInstanceOf(DelegatingLoader::class, $loader);
        $this->assertInstanceOf(LoaderResolver::class, $loader->getResolver());
        $this->assertAttributeCount(3, 'loaders', $loader->getResolver());
    }
}
