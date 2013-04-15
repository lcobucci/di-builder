<?php
namespace Lcobucci\DependencyInjection;

use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStream;

class XmlContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var vfsStreamFile
     */
    private $file;

    public function setUp()
    {
        $this->root = vfsStream::setup('tmp', 0777);
        $this->file = vfsStream::newFile('services.xml', 0755)->at($this->root);
        $this->file->setContent(
            '<?xml version="1.0" encoding="UTF-8"?>
             <container xmlns="http://symfony.com/schema/dic/services"
                 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xsi:schemaLocation="http://symfony.com/schema/dic/services services-1.0.xsd">
                 <services>
        		     <service id="test" class="stdClass" />
        		 </services>
        	 </container>'
        );
    }

    /**
     * @test
     */
    public function dumpMustBeCreatedWhenItDoesNotExists()
    {
        $fileName = 'Project' . md5(vfsStream::url('tmp/services.xml')) . 'ServiceContainer.php';
        $this->assertFalse($this->root->hasChild($fileName));

        $builder = new XmlContainerBuilder(null, vfsStream::url('tmp'));
        $loader = $builder->getContainer(vfsStream::url('tmp/services.xml'));

        $this->assertTrue($this->root->hasChild($fileName));
        $this->assertInstanceOf('\Project7065aa73249af70316e8ccbd4bd3331fServiceContainer', $loader);
    }

    /**
     * @test
     */
    public function dumpShoudlNotBeUpdatedWhenConfigFileHasNotBeenChanged()
    {
        $fileName = 'Project' . md5(vfsStream::url('tmp/services.xml')) . 'ServiceContainer.php';
        $container = vfsStream::newFile($fileName, 0777)->at($this->root);
        $container->setContent($this->getTestDumpContent());

        $builder = new XmlContainerBuilder(null, vfsStream::url('tmp'));
        $loader = $builder->getContainer(vfsStream::url('tmp/services.xml'));

        $this->assertInstanceOf('\Project7065aa73249af70316e8ccbd4bd3331fServiceContainer', $loader);
        $this->assertEquals($this->getTestDumpContent(), $container->getContent());
    }

    /**
     * @test
     */
    public function dumpMustBeUpdatedWhenConfigFileHasBeenChanged()
    {
        $this->file->lastModified(time() + 200);

        $fileName = 'Project' . md5(vfsStream::url('tmp/services.xml')) . 'ServiceContainer.php';

        $container = vfsStream::newFile($fileName, 0777)->at($this->root);
        $container->setContent($this->getTestDumpContent());

        $builder = new XmlContainerBuilder(null, vfsStream::url('tmp'));
        $loader = $builder->getContainer(vfsStream::url('tmp/services.xml'));

        $this->assertTrue($this->root->hasChild($fileName));
        $this->assertInstanceOf('\Project7065aa73249af70316e8ccbd4bd3331fServiceContainer', $loader);
        $this->assertNotEquals($this->getTestDumpContent(), $container->getContent());
    }

    protected function getTestDumpContent()
    {
        return
            '<?php
             use Symfony\Component\DependencyInjection\Container;

             class Project7065aa73249af70316e8ccbd4bd3331fServiceContainer extends Container
             {
                 protected function getTestService() { return $this->services[\'test\'] = new \stdClass(); }
             }';
    }
}
