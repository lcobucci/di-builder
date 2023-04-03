<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Lcobucci\DependencyInjection\ContainerBuilder
 * @covers \Lcobucci\DependencyInjection\Compiler
 * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag
 * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration
 * @covers \Lcobucci\DependencyInjection\Generator
 * @covers \Lcobucci\DependencyInjection\Generators\Xml
 * @covers \Lcobucci\DependencyInjection\Testing\MakeServicesPublic
 */
final class CompilationWithBaseClassTest extends TestCase
{
    use GeneratesDumpDirectory;

    private const DI_NAMESPACE = 'Lcobucci\\DiTests\\BaseClass';

    /** @test */
    public function containerCanHaveACustomBaseClass(): void
    {
        $container = ContainerBuilder::xml(__FILE__, self::DI_NAMESPACE)
            ->setBaseClass(ContainerForTests::class)
            ->setDumpDir($this->dumpDirectory)
            ->getContainer();

        self::assertInstanceOf(ContainerForTests::class, $container);
    }
}
