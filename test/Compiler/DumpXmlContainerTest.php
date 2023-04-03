<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag as Parameters;

#[PHPUnit\CoversClass(DumpXmlContainer::class)]
final class DumpXmlContainerTest extends TestCase
{
    private ConfigCacheInterface&MockObject $configCache;

    #[PHPUnit\Before]
    public function createConfig(): void
    {
        $this->configCache = $this->createMock(ConfigCacheInterface::class);
    }

    #[PHPUnit\Test]
    public function processShouldBeSkippedWhenDevModeIsNotEnabled(): void
    {
        $this->configCache->expects(self::never())
                          ->method('isFresh');

        $this->configCache->expects(self::never())
                          ->method('write');

        $pass = new DumpXmlContainer($this->configCache);
        $pass->process(new ContainerBuilder(new Parameters(['app.devmode' => false])));
    }

    #[PHPUnit\Test]
    public function processShouldBeSkippedWhenCacheIsFresh(): void
    {
        $this->configCache->method('isFresh')
                          ->willReturn(true);

        $this->configCache->expects(self::never())
                          ->method('write');

        $pass = new DumpXmlContainer($this->configCache);
        $pass->process(new ContainerBuilder(new Parameters(['app.devmode' => true])));
    }

    #[PHPUnit\Test]
    public function processShouldDumpTheContainerUsingTheXmlDumper(): void
    {
        $assertXmlHeader = new RegularExpression(
            '#<container xmlns="http://symfony.com/schema/dic/services" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xsi:schemaLocation="http://symfony.com/schema/dic/services '
            . 'https?://symfony.com/schema/dic/services/services-1.0.xsd">#',
        );

        $this->configCache->method('isFresh')
                          ->willReturn(false);

        $this->configCache->expects(self::once())
                          ->method('write')
                          ->with($assertXmlHeader, []);

        $pass = new DumpXmlContainer($this->configCache);
        $pass->process(new ContainerBuilder(new Parameters(['app.devmode' => true])));
    }
}
