<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\CoversClass(Php::class)]
final class PhpTest extends TestCase
{
    #[PHPUnit\Test]
    public function getLoaderShouldReturnAPhpLoader(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Php(__FILE__);

        self::assertInstanceOf(PhpFileLoader::class, $generator->getLoader($container, []));
    }
}
