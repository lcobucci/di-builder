<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use Lcobucci\DependencyInjection\Generator;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\CoversClass(Delegating::class)]
final class DelegatingTest extends TestCase
{
    #[PHPUnit\Test]
    public function getLoaderShouldReturnADelegatingLoaderWithTheOtherLoaders(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $generator = new Delegating(__FILE__);

        $loader = $generator->getLoader($container, []);

        self::assertInstanceOf(DelegatingLoader::class, $loader);

        $resolver = $loader->getResolver();

        self::assertInstanceOf(LoaderResolver::class, $resolver);
        self::assertCount(3, $resolver->getLoaders());
    }
}
