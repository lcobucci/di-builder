# di-builder

[![Total Downloads](https://img.shields.io/packagist/dt/lcobucci/di-builder.svg?style=flat-square)](https://packagist.org/packages/lcobucci/di-builder)
[![Latest Stable Version](https://img.shields.io/packagist/v/lcobucci/di-builder.svg?style=flat-square)](https://packagist.org/packages/lcobucci/di-builder)

[![Build Status](https://img.shields.io/travis/lcobucci/di-builder.svg?style=flat-square)](http://travis-ci.org/#!/lcobucci/di-builder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/lcobucci/di-builder/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/di-builder/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/lcobucci/di-builder/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/lcobucci/di-builder/?branch=master)

This library tries to help the usage of the
[Symfony dependecy injection Component](http://symfony.com/doc/current/components/dependency_injection/introduction.html)
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

## Installation

This package is available on [Packagist](http://packagist.org/packages/lcobucci/di-builder),
you can install it using [Composer](http://getcomposer.org).

```shell
composer require lcobucci/di-builder
```

### PHP Configuration

In order to make sure that we're dealing with the correct data, we're using `assert()`,
which is a very interesting feature in PHP but not often used. The nice thing
about `assert()` is that we can (and should) disable it in production mode so
that we don't have useless statements.

So, for production mode, we recommend you to set `zend.assertions` to `-1` in your `php.ini`.
For development you should leave `zend.assertions` as `1` and set `assert.exception` to `1`, which
will make PHP throw an [`AssertionError`](https://secure.php.net/manual/en/class.assertionerror.php)
when things go wrong.

Check the documentation for more information: https://secure.php.net/manual/en/function.assert.php

## Basic usage

The usage is really simple, just trust the ```Lcobucci\DependencyInjection\Builder``` interface and
all should be good =)

Take a look:

```php
<?php
/* Composer autoloader was required before this */ 

use Your\Own\Compiler\DoSomethingPass;
use Lcobucci\DependencyInjection\ContainerBuilder;
use Lcobucci\DependencyInjection\Generators\Php as PhpGenerator;

$container = (new ContainerBuilder())->setGenerator(new PhpGenerator()) // Changes the generator
                                     ->addFile(__DIR__ . '/config/services.php') // Appends a file to create the container
                                     ->addPath(__DIR__ . '/src/Users/config') // Appends a new path to locate files
                                     ->addFile('services.php') // Appends a file to create the container (to be used with the configured paths)
                                     ->useDevelopmentMode() // Enables the development mode (production is the default)
                                     ->setDumpDir(__DIR__ . '/tmp') // Changes the dump directory
                                     ->setParameter('app.basedir', __DIR__) // Configures a dynamic parameter
                                     ->addPass(new DoSomethingPass()) // Appends a new compiler pass
                                     ->addDelayedPass(DoSomethingPass::class) // Appends a new compiler pass that will only be initialised while building the container
                                     ->addPackage(MyPackage::class) // Appends a new package that might provide files and compiler passes to be added to the the container
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

final class EventListenerInjector implements CompilerPassInterface
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
