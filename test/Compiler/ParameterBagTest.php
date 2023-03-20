<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[PHPUnit\CoversClass(ParameterBag::class)]
final class ParameterBagTest extends TestCase
{
    #[PHPUnit\Test]
    public function setShouldConfigureAParameter(): void
    {
        $pass = new ParameterBag();
        $pass->set('test', 1);

        self::assertEquals(new ParameterBag(['test' => 1]), $pass);
    }

    #[PHPUnit\Test]
    public function getShouldReturnTheValueOfTheParameter(): void
    {
        $pass = new ParameterBag(['test' => 1]);

        self::assertSame(1, $pass->get('test', 2));
    }

    #[PHPUnit\Test]
    public function getShouldReturnTheDefaultValueWhenParameterDoesNotExist(): void
    {
        $pass = new ParameterBag();

        self::assertSame(1, $pass->get('test', 1));
    }

    #[PHPUnit\Test]
    public function invokeShouldAppendAllConfiguredParametersOnTheBuilder(): void
    {
        $builder = new ContainerBuilder();
        $pass    = new ParameterBag(['test' => 1]);

        $pass->process($builder);
        self::assertSame(1, $builder->getParameter('test'));
    }
}
