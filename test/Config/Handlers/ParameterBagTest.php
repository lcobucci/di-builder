<?php
namespace Lcobucci\DependencyInjection\Config\Handlers;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ParameterBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__construct
     */
    public function constructShouldInitializeTheParameters()
    {
        $handler = new ParameterBag(['test' => 1]);

        $this->assertAttributeEquals(['test' => 1], 'parameters', $handler);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__construct
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::set
     */
    public function setShouldConfigureAParameter()
    {
        $handler = new ParameterBag();
        $handler->set('test', 1);

        $this->assertAttributeEquals(['test' => 1], 'parameters', $handler);
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__construct
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::get
     */
    public function getShouldReturnTheValueOfTheParameter()
    {
        $handler = new ParameterBag(['test' => 1]);

        $this->assertEquals(1, $handler->get('test'));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__construct
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::get
     */
    public function getShouldReturnTheDefaultValueWhenParameterDoesNotExist()
    {
        $handler = new ParameterBag();

        $this->assertEquals(1, $handler->get('test', 1));
    }

    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__construct
     * @covers Lcobucci\DependencyInjection\Config\Handlers\ParameterBag::__invoke
     */
    public function invokeShouldAppendAllConfiguredParametersOnTheBuilder()
    {
        $builder = new ContainerBuilder();
        $handler = new ParameterBag(['test' => 1]);

        $handler($builder);
        $this->assertEquals(1, $builder->getParameter('test'));
    }
}
