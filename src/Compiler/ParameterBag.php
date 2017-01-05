<?php
declare(strict_types=1);

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
final class ParameterBag implements CompilerPassInterface
{
    /**
     * @var array
     */
    private $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function set(string $name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function get(string $name, $default = null)
    {
        if (!isset($this->parameters[$name])) {
            return $default;
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $container->getParameterBag()->add($this->parameters);
    }
}
