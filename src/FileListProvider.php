<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Generator as DefaultGenerator;
use Lcobucci\DependencyInjection\Config\Package;

interface FileListProvider extends Package
{
    /**
     * @return DefaultGenerator<string>
     */
    public function getFiles(): DefaultGenerator;
}
