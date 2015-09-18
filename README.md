# di-builder

[![Build Status](https://secure.travis-ci.org/lcobucci/di-builder.png?branch=master)](http://travis-ci.org/#!/lcobucci/di-builder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/lcobucci/di-builder/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/di-builder/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/lcobucci/di-builder/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/di-builder/?branch=master)
[![Total Downloads](https://poser.pugx.org/lcobucci/di-builder/downloads.png)](https://packagist.org/packages/lcobucci/di-builder)
[![Latest Stable Version](https://poser.pugx.org/lcobucci/di-builder/v/stable.png)](https://packagist.org/packages/lcobucci/di-builder)

This library tries to help the usage of the
[Symfony2 dependecy injection Component](http://symfony.com/doc/current/components/dependency_injection/introduction.html)
by offering an easy interface to build and load your dependency injection container.

## Features

 - Multiple container files or paths: you can create the container from one or
   more files and paths, this is useful when dealing with modules;
 - Multiple file loader: you can select if you want to create your container from
   XML (default), YAML, PHP or mixed mode (delegates the loading by the extension of file); 
 - Usage of your own container base class: sometimes you may use a different class
   to inherit your container (rather than ```Symfony\Component\DependencyInjection\Container```;
 - Automatic dump creation: instead of building your container for all requests you
   will do that only when things change (development mode only);
 - Configuration handlers: you are able to inject handlers to be executed before
   the container compilation process (to change the services definitions);
 - Dynamic parameters: if you need to configure parameters that may change according
   with the environment automatically (like base project directory using ```__DIR__```).

## Installation using [composer](http://getcomposer.org/)

Just add ```"lcobucci/di-builder": "~4.0"``` to your composer.json and do a ```composer update``` or you can run:

```bash
composer require lcobucci/di-builder:~4.0
```

## Basic usage

The usage is really simple, just trust the ```Lcobucci\DependencyInjection\Builder``` interface and
all should be good =)

Take a look:

```php
<?php
/* Composer autoloader was required before this */ 

use Lcobucci\DependencyInjection\Compiler\ContainerAware;
use Lcobucci\DependencyInjection\ContainerBuilder;
use Lcobucci\DependencyInjection\Generators\Php as PhpGenerator;

$container = (new ContainerBuilder())->setGenerator(new PhpGenerator()) // Changes the generator
                                     ->addFile(__DIR__ . '/config/services.php') // Appends a file to create the container
                                     ->addPath(__DIR__ . '/src/Users/config') // Appends a new path to locate files
                                     ->addFile('services.php') // Appends a file to create the container (to be used with the configured paths)
                                     ->useDevelopmentMode() // Enables the development mode (production is the default)
                                     ->setDumpDir(__DIR__ . '/tmp') // Changes the dump directory
                                     ->setParameter('app.basedir', __DIR__) // Configures a dynamic parameter
                                     ->addPass(new ContainerAware()) // Appends a new compiler pass
                                     ->getContainer(); // Retrieves the container =)
```

Pretty easy, right?

## Compiler Pass

The handlers are very great to change your container __before__ dumping a container. And
you can create your own handler by just implementing the ```Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface```
interface (so you can also use compiler pass from symfony bundles). Like this:

```php
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EventListenerInjector implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $dispatcher = $container->getDefinition('event.dispatcher');   
    
        foreach ($container->findTaggedServiceIds('event.listener') as $service => $tags) {
            foreach ($tags as $tag) {
                $dispatcher->addMethodCall(
                    'addListener',
                    [$tag['event'], new Reference($service), $tag['priority']]
                );
            }
        }
    }
}
```

Happy coding ;)
