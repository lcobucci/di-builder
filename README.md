# di-builder

master [![Build Status](https://secure.travis-ci.org/lcobucci/di-builder.png?branch=master)](http://travis-ci.org/#!/lcobucci/di-builder)
develop [![Build Status](https://secure.travis-ci.org/lcobucci/di-builder.png?branch=develop)](http://travis-ci.org/#!/lcobucci/di-builder)

[![Code Climate](https://codeclimate.com/github/lcobucci/di-builder/badges/gpa.svg)](https://codeclimate.com/github/lcobucci/di-builder)
[![Test Coverage](https://codeclimate.com/github/lcobucci/di-builder/badges/coverage.svg)](https://codeclimate.com/github/lcobucci/di-builder)
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

Just add ```"lcobucci/di-builder": "~3.0"``` to your composer.json and do a ```composer update``` or you can run:

```bash
composer require lcobucci/di-builder:~3.0
```

## Basic usage

The usage is really simple, just trust the ```Lcobucci\DependencyInjection\Builder``` interface and
all should be good =)

Take a look:

```php
<?php
/* Composer autoloader was required before this */ 

use Lcobucci\DependencyInjection\ContainerBuilder;
use Lcobucci\DependencyInjection\Config\Handlers\ContainerAware;
use Lcobucci\DependencyInjection\Generators\Php as PhpGenerator;

$container = (new ContainerBuilder())->setGenerator(new PhpGenerator()) // Changes the generator
                                     ->addFile(__DIR__ . '/config/services.php') // Appends a file to create the container
                                     ->addPath(__DIR__ . '/src/Users/config') // Appends a new path to locate files
                                     ->addFile('services.php') // Appends a file to create the container (to be used with the configured paths)
                                     ->useDevelopmentMode() // Enables the development mode (production is the default)
                                     ->setDumpDir(__DIR__ . '/tmp') // Changes the dump directory
                                     ->setParameter('app.basedir', __DIR__) // Configures a dynamic parameter
                                     ->addHandler(new ContainerAware()) // Appends a new configuration handler
                                     ->getContainer(); // Retrieves the container =)
```

Pretty easy, right?

## Handlers

The handlers are very great to change your container __before__ the compile process. And
you can create your own handler by just implementing the ```Lcobucci\DependencyInjection\Config\Handler```
interface. Like this:

```php
use Lcobucci\DependencyInjection\Config\Handler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EventListenerInjector implements Handler
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerBuilder $builder)
    {
        $dispatcher = $builder->getDefinition('event.dispatcher');   
    
        foreach ($builder->findTaggedServiceIds('event.listener) as $service => $listenerConfig) {
            $dispatcher->addMethodCall(
                'addListener',
                [$listenerConfig['event'], new Reference($service)], $listenerConfig['priority']
            );
        }
    }
}
```

Happy coding ;)
