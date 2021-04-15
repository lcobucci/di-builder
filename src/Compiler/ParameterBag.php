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
    /** @var array<string, mixed> */
    private array $parameters;

    /** @param array<string, mixed> $parameters */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function set(string $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    public function process(ContainerBuilder $container): void
    {
        $container->getParameterBag()->add($this->parameters);
    }
}
