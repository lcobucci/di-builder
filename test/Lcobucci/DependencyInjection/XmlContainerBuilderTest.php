<?php
namespace Lcobucci\DependencyInjection;

use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\ConfigCache;

class XmlContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var ConfigCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    public function setUp()
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

        $this->root = vfsStream::setup('tmp', 0777, ['services.xml' => $config]);
        $this->cache = $this->getMock('Symfony\Component\Config\ConfigCache', [], [], '', false);
    }

    /**
     * @test
     */
    public function dumpMustBeCreatedWhenItDoesNotExists()
    {
        $data = '';

        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(false);

        $this->cache->expects($this->any())
                    ->method('__toString')
                    ->willReturn(vfsStream::url('tmp/ProjectCache.php'));

        $this->cache->expects($this->once())
                    ->method('write')
                    ->willReturnCallback(function ($content, array $metadata = null) use ($data) {
                    	file_put_contents(vfsStream::url('tmp/ProjectCache.php'), $content);

                    	$data = $content;
                    });

        $builder = new XmlContainerBuilder(vfsStream::url('tmp/services.xml'), $this->cache);
        $container = $builder->getContainer();

        $this->assertInstanceOf('ProjectCache', $container);

        return $data;
    }

    /**
     * @test
     * @depends dumpMustBeCreatedWhenItDoesNotExists
     */
    public function dumpShoudlNotBeUpdatedWhenConfigFileHasNotBeenChanged($content)
    {
        file_put_contents(vfsStream::url('tmp/ProjectCache.php'), $content);

        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(true);

        $this->cache->expects($this->any())
                    ->method('__toString')
                    ->willReturn(vfsStream::url('tmp/ProjectCache.php'));

        $this->cache->expects($this->never())
                    ->method('write');

        $builder = new XmlContainerBuilder(vfsStream::url('tmp/services.xml'), $this->cache);

        $this->assertInstanceOf('ProjectCache', $builder->getContainer());
    }

    /**
     * @test
     */
    public function builderShouldBeAbleToReceiveDefaultParameters()
    {
        $this->cache->expects($this->once())
                    ->method('isFresh')
                    ->willReturn(false);

        $this->cache->expects($this->any())
                    ->method('__toString')
                    ->willReturn(vfsStream::url('tmp/ProjectCacheB.php'));

        $this->cache->expects($this->once())
                    ->method('write')
                    ->willReturnCallback(function ($content, array $metadata = null) {
                    	file_put_contents(vfsStream::url('tmp/ProjectCacheB.php'), $content);
                    });

        $builder = new XmlContainerBuilder(vfsStream::url('tmp/services.xml'), $this->cache);
        $container = $builder->getContainer(array('app.basedir' => 'testing'));

        $this->assertEquals('testing', $container->getParameter('app.basedir'));
    }
}
