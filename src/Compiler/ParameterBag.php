<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Injects parameters into the container
 *
 * You should use this to define dynamic parameters using PHP
 */
final class ParameterBag implements CompilerPassInterface
{
    /**
     * @var array<string, mixed>
     */
    private $parameters;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        if (! isset($this->parameters[$name])) {
            return $default;
        }

        return $this->parameters[$name];
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getParameterBag()->add($this->parameters);
    }
}
