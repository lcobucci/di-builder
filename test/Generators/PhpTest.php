<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class PhpTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     * @covers \Lcobucci\DependencyInjection\Generators\Php::getLoader
     */
    public function getLoaderShouldReturnAPhpLoader(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Php(__FILE__);

        self::assertInstanceOf(PhpFileLoader::class, $generator->getLoader($container, []));
    }
}
