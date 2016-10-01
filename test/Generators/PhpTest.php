<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class PhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     * @covers \Lcobucci\DependencyInjection\Generators\Php::getLoader
     */
    public function getLoaderShouldReturnAPhpLoader()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Php();

        self::assertInstanceOf(PhpFileLoader::class, $generator->getLoader($container, []));
    }
}
