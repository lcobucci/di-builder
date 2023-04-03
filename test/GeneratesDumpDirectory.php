<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use PHPUnit\Framework\Attributes as PHPUnit;

use function assert;
use function exec;
use function is_string;
use function mkdir;
use function tempnam;
use function unlink;

trait GeneratesDumpDirectory
{
    private string $dumpDirectory;

    #[PHPUnit\Before]
    public function createDumpDir(): void
    {
        mkdir(__DIR__ . '/../tmp');

        $tempName = tempnam(__DIR__ . '/../tmp', 'lcobucci-di-builder');
        assert(is_string($tempName));

        $this->dumpDirectory = $tempName;
        unlink($this->dumpDirectory);
        mkdir($this->dumpDirectory);
    }

    #[PHPUnit\After]
    public function removeDumpDir(): void
    {
        exec('rm -rf ' . __DIR__ . '/../tmp');
    }
}
