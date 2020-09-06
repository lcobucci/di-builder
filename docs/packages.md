# Packages

Packages are reusable piece of code that can be plugged into your dependency injection container.
You're likely to use them when creating or consuming libraries.

A package can be a `CompilerPassListProvider` and/or a `FileListProvider`.
The former provides a list of compiler passes (with types and priorities) to be added to the container compilation.
The latter provides a list of files to be loaded for the container.

An example:

```php
<?php
declare(strict_types=1);

namespace MyAwesomeLib\DependencyInjection;

use Generator;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Lcobucci\DependencyInjection\FileListProvider;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

final class MyAwesomeLib implements CompilerPassListProvider, FileListProvider
{
    /** @inheritDoc */
    public function getCompilerPasses(): Generator
    {
        yield [new MyCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5];
    }
    
    /** @inheritDoc */
    public function getFiles() : Generator
    {
        yield dirname(__DIR__) . '/../config/my-awesome-lib.xml';
    }
}
```

## Configuration

When setting up your container, you can call `ContainerBuilder#addPackage()` to register a package - also providing the necessary constructor arguments.
