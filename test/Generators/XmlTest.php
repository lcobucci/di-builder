<?php
namespace Lcobucci\DependencyInjection\Generators;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Generator::__construct
     * @covers Lcobucci\DependencyInjection\Generators\Xml::getLoader
     */
    public function getLoaderShouldReturnAXmlLoader()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Xml();

        self::assertInstanceOf(XmlFileLoader::class, $generator->getLoader($container, []));
    }
}
