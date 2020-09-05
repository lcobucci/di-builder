<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Generator as DefaultGenerator;
use Lcobucci\DependencyInjection\Config\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

interface CompilerPassListProvider extends Package
{
    /** @return DefaultGenerator<array{0: CompilerPassInterface, 1?: string, 2?: int}> */
    public function getCompilerPasses(): DefaultGenerator;
}
