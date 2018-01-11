<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\XmlDumper;

final class DumpXmlContainer implements CompilerPassInterface
{
    /**
     * @var ConfigCacheInterface
     */
    private $configCache;

    public function __construct(ConfigCacheInterface $configCache)
    {
        $this->configCache = $configCache;
    }

    public function process(ContainerBuilder $container): void
    {
        if (! $container->getParameter('app.devmode') || ! $this->configCache->isFresh()) {
            return;
        }

        $this->configCache->write((new XmlDumper($container))->dump(), $container->getResources());
    }
}
