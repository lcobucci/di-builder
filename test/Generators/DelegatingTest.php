<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Generators;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DelegatingTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Generator::__construct
     * @covers \Lcobucci\DependencyInjection\Generators\Delegating::getLoader
     */
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
