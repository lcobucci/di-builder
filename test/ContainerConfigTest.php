<?php
namespace Lcobucci\DependencyInjection;

use org\bovigo\vfs\vfsStream;

class ContainerConfigTest extends \PHPUnit_Framework_TestCase
{
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
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function constructShouldRaiseExceptionWhenFilePathIsInvalid()
    {
        new ContainerConfig(vfsStream::url('tmp/s.xml'));
    }

    /**
     * @test
     */
    public function constructMustConfigureTheAttributesAccordingWithTheGivenArguments()
    {
        $config = new ContainerConfig(
            vfsStream::url('tmp/services.xml'),
            vfsStream::url('tmp'),
            array('test' => 1),
            'Test',
            false,
            array(vfsStream::url('tmp'))
        );

        $this->assertAttributeEquals(vfsStream::url('tmp/services.xml'), 'file', $config);
        $this->assertAttributeEquals(md5(false), 'pathId', $config);
        $this->assertAttributeEquals(vfsStream::url('tmp'), 'cacheDir', $config);
        $this->assertAttributeEquals(array('app.devmode' => false, 'test' => 1), 'defaultParameters', $config);
        $this->assertAttributeEquals('Test', 'baseClass', $config);
        $this->assertAttributeEquals(array(vfsStream::url('tmp')), 'paths', $config);

        return $config;
    }

    /**
     * @test
     * @depends constructMustConfigureTheAttributesAccordingWithTheGivenArguments
     */
    public function getFileMustReturnTheConfiguredValue(ContainerConfig $config)
    {
        $this->assertEquals(vfsStream::url('tmp/services.xml'), $config->getFile());
    }

    /**
     * @test
     * @depends constructMustConfigureTheAttributesAccordingWithTheGivenArguments
     */
    public function getDefaultParametersMustReturnTheConfiguredValue(ContainerConfig $config)
    {
        $this->assertEquals(array('app.devmode' => false, 'test' => 1), $config->getDefaultParameters());
    }

    /**
     * @test
     * @depends constructMustConfigureTheAttributesAccordingWithTheGivenArguments
     */
    public function getBaseClassMustReturnTheConfiguredValue(ContainerConfig $config)
    {
        $this->assertEquals('Test', $config->getBaseClass());
    }

    /**
     * @test
     * @depends constructMustConfigureTheAttributesAccordingWithTheGivenArguments
     */
    public function getPathsMustReturnTheConfiguredValue(ContainerConfig $config)
    {
        $this->assertEquals(array(vfsStream::url('tmp')), $config->getPaths());
    }

    /**
     * @test
     * @depends constructMustConfigureTheAttributesAccordingWithTheGivenArguments
     */
    public function getClassNameMustReturnADefaultNameWithAHashFromTheFilePath(ContainerConfig $config)
    {
        $this->assertEquals('Projectd41d8cd98f00b204e9800998ecf8427eServiceContainer', $config->getClassName());
    }

    /**
     * @test
     * @depends constructMustConfigureTheAttributesAccordingWithTheGivenArguments
     */
    public function getCacheMustReturnAConfigCacheBasedOnContainerConfiguration(ContainerConfig $config)
    {
        $cache = $config->getCache();

        $this->assertInstanceOf('Symfony\Component\Config\ConfigCache', $cache);
        $this->assertSame($cache, $config->getCache());

        $this->assertAttributeEquals(vfsStream::url('tmp/Projectd41d8cd98f00b204e9800998ecf8427eServiceContainer.php'), 'file', $cache);
        $this->assertAttributeEquals(false, 'debug', $cache);
    }

    /**
     * @test
     */
    public function constructMustConfigureTheAttributesWithDefaultValuesWhenArgumentsWereSupressed()
    {
        $config = new ContainerConfig(vfsStream::url('tmp/services.xml'));

        $this->assertAttributeEquals(vfsStream::url('tmp/services.xml'), 'file', $config);
        $this->assertAttributeEquals(md5(false), 'pathId', $config);
        $this->assertAttributeEquals(sys_get_temp_dir(), 'cacheDir', $config);
        $this->assertAttributeEquals(array('app.devmode' => true), 'defaultParameters', $config);
        $this->assertAttributeEquals(null, 'baseClass', $config);
        $this->assertAttributeEquals(array(), 'paths', $config);
    }
}
