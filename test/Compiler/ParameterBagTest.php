<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @coversDefaultClass \Lcobucci\DependencyInjection\Compiler\ParameterBag */
final class ParameterBagTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::set
     */
    public function setShouldConfigureAParameter(): void
    {
        $pass = new ParameterBag();
        $pass->set('test', 1);

        self::assertEquals(new ParameterBag(['test' => 1]), $pass);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::get
     */
    public function getShouldReturnTheValueOfTheParameter(): void
    {
        $pass = new ParameterBag(['test' => 1]);

        self::assertSame(1, $pass->get('test', 2));
    }

    /**
     * @test
     *
     * @covers ::get
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     */
    public function getShouldReturnTheDefaultValueWhenParameterDoesNotExist(): void
    {
        $pass = new ParameterBag();

        self::assertSame(1, $pass->get('test', 1));
    }

    /**
     * @test
     *
     * @covers ::process
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     */
    public function invokeShouldAppendAllConfiguredParametersOnTheBuilder(): void
    {
        $builder = new ContainerBuilder();
        $pass    = new ParameterBag(['test' => 1]);

        $pass->process($builder);
        self::assertSame(1, $builder->getParameter('test'));
    }
}
