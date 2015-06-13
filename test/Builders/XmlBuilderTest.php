<?php
namespace Lcobucci\DependencyInjection\Builders;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\ConfigCache;

class XmlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var ContainerConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    protected function setUp()
    {
        $config = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services services-1.0.xsd">
    <parameters>
        <parameter key="test">test</parameter>
    </parameters>
    <services>
        <service id="test" class="stdClass" />
    </services>
</container>
XML;

        vfsStream::setup('tmp', 0777, array('services.xml' => $config));

        $this->cache = $this->getMock('Symfony\Component\Config\ConfigCache', array(), array(), '', false);
        $this->config = $this->getMock('Lcobucci\DependencyInjection\ContainerConfig', array(), array(), '', false);

        $this->config->expects($this->any())
                     ->method('getCache')
                     ->willReturn($this->cache);

        $this->config->expects($this->any())
                     ->method('getFile')
                     ->willReturn(vfsStream::url('tmp/services.xml'));

        $this->config->expects($this->any())
                     ->method('getPaths')
                     ->willReturn(array());
    }

    /**
     * @test
     */
    public function getContainerMustCreateWhenItDoesNotExists()
    {
        $this->config->expects($this->any())
                     ->method('getDefaultParameters')
                     ->willReturn(array());

        $this->config->expects($this->any())
                     ->method('getClassName')
                     ->willReturn('ProjectCache');

        $data = '';

        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(false);

        $this->cache->expects($this->any())
                    ->method('getPath')
                    ->willReturn(vfsStream::url('tmp/ProjectCache.php'));

        $this->cache->expects($this->once())
                    ->method('write')
                    ->willReturnCallback(function ($content, array $metadata = null) use ($data) {
                    	file_put_contents(vfsStream::url('tmp/ProjectCache.php'), $content);

                    	$data = $content;
                    });

        $builder = new XmlBuilder();
        $container = $builder->getContainer($this->config);

        $this->assertInstanceOf('ProjectCache', $container);

        return $data;
    }

    /**
     * @test
     * @depends getContainerMustCreateWhenItDoesNotExists
     */
    public function getContainerMustNotUpdateWhenConfigFileHasNotBeenChanged($content)
    {
        file_put_contents(vfsStream::url('tmp/ProjectCache.php'), $content);

        $this->config->expects($this->any())
                     ->method('getDefaultParameters')
                     ->willReturn(array());

        $this->config->expects($this->any())
                     ->method('getClassName')
                     ->willReturn('ProjectCache');

        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(true);

        $this->cache->expects($this->any())
                    ->method('getPath')
                    ->willReturn(vfsStream::url('tmp/ProjectCache.php'));

        $this->cache->expects($this->never())
                    ->method('write');

        $builder = new XmlBuilder();

        $this->assertInstanceOf('ProjectCache', $builder->getContainer($this->config));
    }

    /**
     * @test
     */
    public function getContainerShouldBeAbleToReceiveDefaultParameters()
    {
        $this->config->expects($this->any())
                     ->method('getDefaultParameters')
                     ->willReturn(array('app.basedir' => 'testing'));

        $this->config->expects($this->any())
                     ->method('getClassName')
                     ->willReturn('ProjectCacheB');

        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(false);

        $this->cache->expects($this->any())
                    ->method('getPath')
                    ->willReturn(vfsStream::url('tmp/ProjectCacheB.php'));

        $this->cache->expects($this->once())
                    ->method('write')
                    ->willReturnCallback(function ($content, array $metadata = null) {
                    	file_put_contents(vfsStream::url('tmp/ProjectCacheB.php'), $content);
                    });

        $builder = new XmlBuilder();
        $container = $builder->getContainer($this->config);

        $this->assertEquals('testing', $container->getParameter('app.basedir'));
    }

    /**
     * @test
     * @depends getContainerShouldBeAbleToReceiveDefaultParameters
     */
    public function getContainerShouldBeAbleToReceiveABaseClass()
    {
        $this->config->expects($this->any())
                     ->method('getDefaultParameters')
                     ->willReturn(array());

        $this->config->expects($this->any())
                     ->method('getBaseClass')
                     ->willReturn('ProjectCacheB');

        $this->config->expects($this->any())
                     ->method('getClassName')
                     ->willReturn('ProjectCacheC');

        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(false);

        $this->cache->expects($this->any())
                    ->method('getPath')
                    ->willReturn(vfsStream::url('tmp/ProjectCacheC.php'));

        $this->cache->expects($this->once())
                    ->method('write')
                    ->willReturnCallback(function ($content, array $metadata = null) {
                    	file_put_contents(vfsStream::url('tmp/ProjectCacheC.php'), $content);
                    });

        $builder = new XmlBuilder();
        $container = $builder->getContainer($this->config);

        $this->assertInstanceOf('ProjectCacheB', $container);
        $this->assertInstanceOf('ProjectCacheC', $container);
    }
}
