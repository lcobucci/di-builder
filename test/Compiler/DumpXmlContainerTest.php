<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Compiler;

use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag as Parameters;

final class DumpXmlContainerTest extends TestCase
{
    /**
     * @var ConfigCacheInterface|MockObject
     */
    private $configCache;

    /**
     * @before
     */
    public function createConfig(): void
    {
        $this->configCache = $this->createMock(ConfigCacheInterface::class);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer::__construct
     * @covers \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer::process
     */
    public function processShouldBeSkippedWhenDevModeIsNotEnabled(): void
    {
        $this->configCache->expects($this->never())
                          ->method('isFresh');

        $this->configCache->expects($this->never())
                          ->method('write');

        $pass = new DumpXmlContainer($this->configCache);
        $pass->process(new ContainerBuilder(new Parameters(['app.devmode' => false])));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer::__construct
     * @covers \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer::process
     */
    public function processShouldBeSkippedWhenCacheIsNotFresh(): void
    {
        $this->configCache->method('isFresh')
                          ->willReturn(false);

        $this->configCache->expects($this->never())
                          ->method('write');

        $pass = new DumpXmlContainer($this->configCache);
        $pass->process(new ContainerBuilder(new Parameters(['app.devmode' => true])));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer::__construct
     * @covers \Lcobucci\DependencyInjection\Compiler\DumpXmlContainer::process
     */
    public function processShouldDumpTheContainerUsingTheXmlDumper(): void
    {
        $assertXmlHeader = new StringContains(
            '<container xmlns="http://symfony.com/schema/dic/services" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xsi:schemaLocation="http://symfony.com/schema/dic/services '
            . 'http://symfony.com/schema/dic/services/services-1.0.xsd">'
        );

        $this->configCache->method('isFresh')
                          ->willReturn(true);

        $this->configCache->expects($this->once())
                          ->method('write')
                          ->with($assertXmlHeader, []);

        $pass = new DumpXmlContainer($this->configCache);
        $pass->process(new ContainerBuilder(new Parameters(['app.devmode' => true])));
    }
}
