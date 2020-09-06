# Compiler Passes

Compiler passes are components that hook into the container compilation process and add/modify service definitions.
They're extremely handy to dynamically configure your services.

For example, if you want to automatically register all services tagged with `event.listener` to the event dispatcher, you may use the follow compiler pass:

```php
<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EventListenerInjector implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $dispatcher = $container->getDefinition(EventDispatcherInterface::class);   
    
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

## Type and priority

There are different types of compiler passes.
You can see the available types on the [`PassConfig`](https://github.com/symfony/dependency-injection/blob/6152c7f22d9d2f367534144a75a54e27f51e6f44/Compiler/PassConfig.php#L25-L29) class.

You may also configure different priorities for the compiler passes.

## Configuration

When setting up your container, you can call `ContainerBuilder#addPass()` or `ContainerBuilder#addDelayedPass()`.

The type and priority are configured via these methods as well.
The default values are `PassConfig::TYPE_BEFORE_OPTIMIZATION` as type and `0` as priority.

!!! Important
    The method `ContainerBuilder#addDelayedPass()` was introduced on [v5.2.0], to avoid the creation of **unnecessary instances**.
    It's recommended to use it because it (possibly) makes your application faster by reducing the number of loaded classes and instantiated objects during the bootstrap.

[v5.2.0]: https://github.com/lcobucci/di-builder/releases/tag/5.2.0
