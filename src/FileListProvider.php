<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use Lcobucci\DependencyInjection\Config\Package;

interface FileListProvider extends Package
{
    public function getFiles(): \Generator;
}
