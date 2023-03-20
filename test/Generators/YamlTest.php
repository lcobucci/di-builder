<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\CoversClass(Yaml::class)]
final class YamlTest extends TestCase
{
    #[PHPUnit\Test]
    public function getLoaderShouldReturnAYamlLoader(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Yaml(__FILE__);

        self::assertInstanceOf(YamlFileLoader::class, $generator->getLoader($container, []));
    }
}
