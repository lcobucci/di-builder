<?php
namespace Lcobucci\DependencyInjection\Config\Handlers;

use Lcobucci\DependencyInjection\Config\Handler;
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
class ContainerAware implements Handler
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerBuilder $builder)
    {
        $container = $this->createContainerReference();

        foreach ($builder->getDefinitions() as $definition) {
            $this->injectContainer($definition, $container);
        }
    }

    /**
     * @return Reference
     */
    protected function createContainerReference()
    {
        return new Reference('service_container');
    }

    /**
     * @param Definition $definition
     * @param Reference $container
     */
    private function injectContainer(Definition $definition, Reference $container)
    {
        $class = $definition->getClass();

        if (empty($class) || !is_subclass_of($class, $this->getInterfaceName())) {
            return ;
        }

        $definition->addMethodCall('setContainer', [$container]);
    }

    /**
     * @return string
     */
    protected function getInterfaceName()
    {
        return 'Symfony\Component\DependencyInjection\ContainerAwareInterface';
    }
}
