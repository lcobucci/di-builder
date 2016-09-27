<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class ParameterBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     */
    public function constructShouldInitializeTheParameters()
    {
        $pass = new ParameterBag(['test' => 1]);

        self::assertAttributeEquals(['test' => 1], 'parameters', $pass);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag::set
     */
    public function setShouldConfigureAParameter()
    {
        $pass = new ParameterBag();
        $pass->set('test', 1);

        self::assertAttributeEquals(['test' => 1], 'parameters', $pass);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag::get
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     */
    public function getShouldReturnTheValueOfTheParameter()
    {
        $pass = new ParameterBag(['test' => 1]);

        self::assertEquals(1, $pass->get('test'));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag::get
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     */
    public function getShouldReturnTheDefaultValueWhenParameterDoesNotExist()
    {
        $pass = new ParameterBag();

        self::assertEquals(1, $pass->get('test', 1));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\ParameterBag::process
     *
     * @uses \Lcobucci\DependencyInjection\Compiler\ParameterBag::__construct
     */
    public function invokeShouldAppendAllConfiguredParametersOnTheBuilder()
    {
        $builder = new ContainerBuilder();
        $pass = new ParameterBag(['test' => 1]);

        $pass->process($builder);
        self::assertEquals(1, $builder->getParameter('test'));
    }
}
