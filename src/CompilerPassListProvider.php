<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\Package;

interface CompilerPassListProvider extends Package
{
    public function getCompilerPasses(): \Generator;
}
