<?php
namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This handlers finds all definitions of classes that uses ContainerAwareInterface and adds
 * the reference of the container automatically
 *
 * It is not wise to use the ContainerAwareInterface on your classes, it brings a huge mess
 * into your project and hide the real dependecies from you, but some people relies on that
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ContainerAware implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $ref = new Reference('service_container');

        foreach ($container->getDefinitions() as $definition) {
            $this->injectContainer($definition, $ref);
        }
    }

    /**
     * @param Definition $definition
     * @param Reference $container
     */
    private function injectContainer(Definition $definition, Reference $container)
    {
        $class = $definition->getClass();

        if (empty($class) || !is_subclass_of($class, ContainerAwareInterface::class)) {
            return ;
        }

        $definition->addMethodCall('setContainer', [$container]);
    }
}
