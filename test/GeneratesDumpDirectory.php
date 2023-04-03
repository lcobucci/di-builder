<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection;

use function assert;
use function exec;
use function is_string;
use function mkdir;
use function tempnam;
use function unlink;

trait GeneratesDumpDirectory
{
    private string $dumpDirectory;

    /** @before */
    public function createDumpDir(): void
    {
        mkdir(__DIR__ . '/../tmp');

        $tempName = tempnam(__DIR__ . '/../tmp', 'lcobucci-di-builder');
        assert(is_string($tempName));

        $this->dumpDirectory = $tempName;
        unlink($this->dumpDirectory);
        mkdir($this->dumpDirectory);
    }

    /** @after */
    public function removeDumpDir(): void
    {
        exec('rm -rf ' . __DIR__ . '/../tmp');
    }
}
