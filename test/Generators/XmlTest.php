<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\CoversClass(Xml::class)]
final class XmlTest extends TestCase
{
    #[PHPUnit\Test]
    public function getLoaderShouldReturnAXmlLoader(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Xml(__FILE__);

        self::assertInstanceOf(XmlFileLoader::class, $generator->getLoader($container, []));
    }
}
