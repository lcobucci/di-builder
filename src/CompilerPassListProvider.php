<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Generator as DefaultGenerator;
use Lcobucci\DependencyInjection\Config\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

interface CompilerPassListProvider extends Package
{
    /**
     * @return DefaultGenerator<array<CompilerPassInterface|string|int|array<string|array<mixed>>>>
     */
    public function getCompilerPasses(): DefaultGenerator;
}
