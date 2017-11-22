<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\FileListProvider;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
final class ContainerConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompilerPassInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pass;

    /**
     * @before
     */
    public function configureDependencies(): void
    {
        $this->pass = $this->createMock(CompilerPassInterface::class);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function constructShouldConfigureTheAttributes(): void
    {
        $package = get_class($this->createMock(Package::class));

        $config = new ContainerConfiguration(
            ['services.xml'],
            [[$this->pass, 'beforeOptimization']],
            ['test'],
            [[$package, []]]
        );

        self::assertAttributeEquals(['services.xml'], 'files', $config);
        self::assertAttributeSame([[$this->pass, 'beforeOptimization']], 'passList', $config);
        self::assertAttributeEquals(['test'], 'paths', $config);
        self::assertAttributeEquals([[$package, []]], 'packages', $config);
        self::assertAttributeEquals(sys_get_temp_dir(), 'dumpDir', $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getFiles
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackagesThatProvideFiles
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterModules
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getFilesShouldReturnTheFileList(): void
    {
        $config = new ContainerConfiguration(['services.xml']);

        self::assertSame(['services.xml'], iterator_to_array($config->getFiles()));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getFiles
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackagesThatProvideFiles
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterModules
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getFilesShouldYieldTheFilesFromPackagesFirst(): void
    {
        $package = new class implements FileListProvider
        {
            public function getFiles(): \Generator
            {
                yield 'services2.xml';
            }
        };

        $config = new ContainerConfiguration(['services.xml'], [], [], [[get_class($package), []]]);

        self::assertSame(['services2.xml', 'services.xml'], iterator_to_array($config->getFiles(), false));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addFile
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function addFileShouldAppendANewFileToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addFile('services.xml');

        self::assertAttributeEquals(['services.xml'], 'files', $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPassList
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackagesThatProvideCompilerPasses
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterModules
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPassListShouldReturnTheHandlersList(): void
    {
        $config = new ContainerConfiguration([], [[$this->pass, 'beforeOptimization']]);

        self::assertSame([[$this->pass, 'beforeOptimization']], iterator_to_array($config->getPassList()));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPassList
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackagesThatProvideCompilerPasses
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterModules
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPassListShouldYieldTheCompilerPassesFromPackagesFirst(): void
    {
        $package = new class implements CompilerPassListProvider
        {
            public function getCompilerPasses(): \Generator
            {
                yield [CompilerPassInterface::class, 'beforeOptimization'];
            }
        };

        $config = new ContainerConfiguration(
            [],
            [[$this->pass, 'beforeOptimization']],
            [],
            [[get_class($package), []]]
        );

        self::assertSame(
            [
                [CompilerPassInterface::class, 'beforeOptimization'],
                [$this->pass, 'beforeOptimization'],
            ],
            iterator_to_array($config->getPassList(), false)
        );
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPass
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function addPassShouldAppendANewHandlerToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addPass($this->pass);

        self::assertAttributeSame([[$this->pass, 'beforeOptimization']], 'passList', $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addDelayedPass
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function addDelayedPassShouldAppendANewCompilerPassToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b']);

        self::assertAttributeSame(
            [[[ParameterBag::class, ['a' => 'b']], 'beforeOptimization']],
            'passList',
            $config
        );
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPackage
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function addPackageShouldAppendThePackageConfigurationToTheList(): void
    {
        $package = get_class($this->createMock(Package::class));
        $config  = new ContainerConfiguration();

        $config->addPackage($package, ['a' => 'b']);

        self::assertAttributeSame([[$package, ['a' => 'b']]], 'packages', $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPackagesShouldReturnAListOfInstantiatedPackages(): void
    {
        $package = $this->createMock(Package::class);
        $config  = new ContainerConfiguration([], [], [], [[get_class($package), []]]);

        self::assertEquals([$package], $config->getPackages());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPackagesShouldInstantiateThePackagesOnlyOnce(): void
    {
        $packageName = get_class($this->createMock(Package::class));
        $config      = new ContainerConfiguration([], [], [], [[$packageName, []]]);

        self::assertSame($config->getPackages(), $config->getPackages());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPaths
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPathsShouldReturnThePathsList()
    {
        $config = new ContainerConfiguration([], [], ['config']);

        self::assertEquals(['config'], $config->getPaths());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPath
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function addPathShouldAppendANewPathToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addPath('services');

        self::assertAttributeEquals(['services'], 'paths', $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::setBaseClass
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function setBaseClassShouldChangeTheAttribute(): ContainerConfiguration
    {
        $config = new ContainerConfiguration();
        $config->setBaseClass('Test');

        self::assertAttributeEquals('Test', 'baseClass', $config);

        return $config;
    }

    /**
     * @test
     *
     * @depends setBaseClassShouldChangeTheAttribute
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getBaseClass
     */
    public function getBaseClassShouldReturnTheAttributeValue(ContainerConfiguration $config): void
    {
        self::assertEquals('Test', $config->getBaseClass());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpDir
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getDumpDirShouldReturnTheAttributeValue(): void
    {
        $config = new ContainerConfiguration();

        self::assertEquals(sys_get_temp_dir(), $config->getDumpDir());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::setDumpDir
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function setDumpDirShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration();
        $config->setDumpDir('/test/');

        self::assertAttributeEquals('/test', 'dumpDir', $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getClassName
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getClassNameShouldCreateAHashFromFilesAndPaths(): void
    {
        $config = new ContainerConfiguration(['services.xml'], [], ['config']);

        self::assertEquals(
            'Project' . md5(implode(';', ['services.xml', 'config'])) . 'ServiceContainer',
            $config->getClassName()
        );
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpFile
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getClassName
     */
    public function getDumpFileShouldReturnTheFullPathOfDumpFile(): void
    {
        $config = new ContainerConfiguration();

        self::assertEquals(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Project' . md5('') . 'ServiceContainer.php',
            $config->getDumpFile()
        );
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpFile
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getClassName
     */
    public function getDumpFileCanAlsoAddPrefixForTheFile(): void
    {
        $config = new ContainerConfiguration();

        self::assertEquals(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_Project' . md5('') . 'ServiceContainer.php',
            $config->getDumpFile('test_')
        );
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpOptions
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getClassName
     */
    public function getDumpOptionsShouldReturnTheDumpingInformation(): void
    {
        $config  = new ContainerConfiguration();
        $options = ['class' => 'Project' . md5('') . 'ServiceContainer'];

        self::assertEquals($options, $config->getDumpOptions());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::setBaseClass
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpOptions
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getClassName
     */
    public function getDumpOptionsShouldIncludeBaseWhenWasConfigured(): void
    {
        $config = new ContainerConfiguration();
        $config->setBaseClass('Test');
        $options = ['class' => 'Project' . md5('') . 'ServiceContainer', 'base_class' => 'Test'];

        self::assertEquals($options, $config->getDumpOptions());
    }
}
