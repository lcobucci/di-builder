<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class YamlTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     * @covers \Lcobucci\DependencyInjection\Generators\Yaml::getLoader
     */
    public function getLoaderShouldReturnAYamlLoader(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Yaml();

        self::assertInstanceOf(YamlFileLoader::class, $generator->getLoader($container, []));
    }
}
