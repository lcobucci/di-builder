<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\Config\ContainerConfiguration;
use Lcobucci\DependencyInjection\Generators\Xml;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;

#[PHPUnit\CoversClass(ContainerBuilder::class)]
#[PHPUnit\CoversClass(Compiler::class)]
#[PHPUnit\CoversClass(ParameterBag::class)]
#[PHPUnit\CoversClass(ContainerConfiguration::class)]
#[PHPUnit\CoversClass(Generator::class)]
#[PHPUnit\CoversClass(Xml::class)]
final class CompilationWithBaseClassTest extends TestCase
{
    use GeneratesDumpDirectory;

    private const DI_NAMESPACE = 'Lcobucci\\DiTests\\BaseClass';

    #[PHPUnit\Test]
    public function containerCanHaveACustomBaseClass(): void
    {
        $container = ContainerBuilder::xml(__FILE__, self::DI_NAMESPACE)
            ->setBaseClass(ContainerForTests::class)
            ->setDumpDir($this->dumpDirectory)
            ->getContainer();

        self::assertInstanceOf(ContainerForTests::class, $container);
    }
}
