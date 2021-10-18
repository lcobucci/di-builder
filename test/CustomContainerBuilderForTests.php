<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyBuilder;

final class CustomContainerBuilderForTests extends SymfonyBuilder
{
    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        $this->parameterBag->set('built-with-very-special-builder', true);

        parent::compile($resolveEnvPlaceholders);
    }
}
