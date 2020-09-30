# Configuration

Setting up the container can be a complicated task.
In order to make the it easier, we provide a builder. 

The builder is initialised via a named constructor using XML as the default format.
Once everything is configured, the builder gives you a fully functional container:

```php
<?php
declare(strict_types=1);

namespace Me\MyApplication\DI;

use Lcobucci\DependencyInjection\ContainerBuilder;

// The path to the current file is passed so we can track changes
// to it and refresh the cache (for development mode)
$builder = ContainerBuilder::default(__FILE__, __NAMESPACE__);

$container = $builder->getContainer();

// Alternatively, you may use the method `ContainerBuilder#getTestContainer()`,
// so it can be use for tests that need a real container:
$testContainer = $builder->getTestContainer();
```

## Available methods

* `ContainerBuilder#setGenerator()`: Modifies the generator to be used.
    We support the `XML`, `Yaml`, `PHP`, and `Delegating` generators (the latter allows to use all formats together)
* `ContainerBuilder#addPath()`: Add a base path to find files
* `ContainerBuilder#addFile()`: Adds a container source file
* `ContainerBuilder#addPass()`: Adds an instance of a [Compiler Pass](compiler-passes.md) to be processed
* `ContainerBuilder#addDelayedPass()`: Adds a reference (class name and constructor arguments) of a [Compiler Pass](compiler-passes.md) to be processed
* `ContainerBuilder#addPackage()`: Adds a reference (class name and constructor arguments) of a [Package](packages.md) to be processed
* `ContainerBuilder#useDevelopmentMode()`: Optimises the generate container for development purposes (configures the compiler to track file changes and update the cache)
* `ContainerBuilder#setDumpDir()`: Configures the directory to be used to dump the cache files
* `ContainerBuilder#setParameter()`: Configures a dynamic parameter
* `ContainerBuilder#setBaseClass()`: Modifies which class should be used as base class for the container

## Configuration file example

You may use the following configuration file as inspiration for your projects (usually placed in the `/config` folder):

```php
<?php
declare(strict_types=1);

namespace Me\MyApplication\DI;

use Lcobucci\DependencyInjection\ContainerBuilder;

use function dirname;
use function getenv;

require __DIR__ . '/../vendor/autoload.php';

$builder     = ContainerBuilder::default(__FILE__, __NAMESPACE__);
$projectRoot = dirname(__DIR__);

if (getenv('APPLICATION_MODE', true) === 'development') {
    $builder->useDevelopmentMode();
}

return $builder->setDumpDir($projectRoot . '/var/tmp')
               ->setParameter('app.basedir', $projectRoot)
               ->addFile(__DIR__ . '/container.xml')
               ->getContainer();
```
