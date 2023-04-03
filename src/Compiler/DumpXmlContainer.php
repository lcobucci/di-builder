<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\XmlDumper;

final class DumpXmlContainer implements CompilerPassInterface
{
    public function __construct(private ConfigCacheInterface $configCache)
    {
    }

    public function process(ContainerBuilder $container): void
    {
        if ($container->getParameter('app.devmode') === false || $this->configCache->isFresh()) {
            return;
        }

        $this->configCache->write((new XmlDumper($container))->dump(), $container->getResources());
    }
}
