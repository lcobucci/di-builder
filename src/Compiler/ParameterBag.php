<?php
namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Injects parameters into the container
 *
 * You should use this to define dynamic parameters using PHP
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class ParameterBag implements CompilerPassInterface
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (!isset($this->parameters[$name])) {
            return $default;
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->getParameterBag()->add($this->parameters);
    }
}
