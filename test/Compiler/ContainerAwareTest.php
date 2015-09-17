<?php
namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerAware as ContainerAwareClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ContainerAwareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @covers Lcobucci\DependencyInjection\Compiler\ContainerAware::process
     * @covers Lcobucci\DependencyInjection\Compiler\ContainerAware::injectContainer
     */
    public function processShouldAppendTheSetContainerMethodWhenNeeded()
    {
        $builder = $this->createBuilder();
        $pass = new ContainerAware();

        $pass->process($builder);
    }

    /**
     * @return ContainerBuilder
     */
    private function createBuilder()
    {
        $definition1 = $this->getMock(Definition::class);
        $definition2 = clone $definition1;

        $builder = new ContainerBuilder();
        $builder->addDefinitions([$definition1, $definition2]);

        $definition1->expects($this->any())
                    ->method('getClass')
                    ->willReturn(__CLASS__);

        $definition2->expects($this->any())
                    ->method('getClass')
                    ->willReturn(ContainerAwareClass::class);

        $definition1->expects($this->never())
                    ->method('addMethodCall');

        $definition2->expects($this->once())
                    ->method('addMethodCall');

        return $builder;
    }
}
