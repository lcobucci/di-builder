<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Generator;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Lcobucci\DependencyInjection\FileListProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use function get_class;
use function implode;
use function iterator_to_array;
use function md5;
use function sys_get_temp_dir;
use const DIRECTORY_SEPARATOR;

final class ContainerConfigurationTest extends TestCase
{
    /**
     * @var CompilerPassInterface|MockObject
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
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getFiles
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
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
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getFilesShouldYieldTheFilesFromPackagesFirst(): void
    {
        $package1 = new class implements CompilerPassListProvider
        {
            public function getCompilerPasses(): Generator
            {
                yield [CompilerPassInterface::class, 'beforeOptimization'];
            }
        };

        $package2 = new class implements FileListProvider
        {
            public function getFiles(): Generator
            {
                yield 'services2.xml';
            }
        };

        $config = new ContainerConfiguration(
            ['services.xml'],
            [],
            [],
            [[get_class($package1), []], [get_class($package2), []]]
        );

        self::assertSame(['services2.xml', 'services.xml'], iterator_to_array($config->getFiles(), false));
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addFile
     */
    public function addFileShouldAppendANewFileToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addFile('services.xml');

        self::assertEquals(new ContainerConfiguration(['services.xml']), $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPassList
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterPackages
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
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::filterPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPassListShouldYieldTheCompilerPassesFromPackagesFirst(): void
    {
        $package1 = new class implements CompilerPassListProvider
        {
            public function getCompilerPasses(): Generator
            {
                yield [CompilerPassInterface::class, 'beforeOptimization'];
            }
        };

        $package2 = new class implements FileListProvider
        {
            public function getFiles(): Generator
            {
                yield 'services2.xml';
            }
        };

        $config = new ContainerConfiguration(
            [],
            [[$this->pass, 'beforeOptimization']],
            [],
            [[get_class($package1), []], [get_class($package2), []]]
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
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPass
     */
    public function addPassShouldAppendANewHandlerToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addPass($this->pass);

        $expected = new ContainerConfiguration(
            [],
            [[$this->pass, PassConfig::TYPE_BEFORE_OPTIMIZATION, 0]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPass
     */
    public function addPassCanReceiveTheTypeAndPriority(): void
    {
        $config = new ContainerConfiguration();
        $config->addPass($this->pass, PassConfig::TYPE_AFTER_REMOVING, 1);

        $expected = new ContainerConfiguration(
            [],
            [[$this->pass, PassConfig::TYPE_AFTER_REMOVING, 1]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addDelayedPass
     */
    public function addDelayedPassShouldAppendANewCompilerPassToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b']);

        $expected = new ContainerConfiguration(
            [],
            [[[ParameterBag::class, ['a' => 'b']], PassConfig::TYPE_BEFORE_OPTIMIZATION, 0]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addDelayedPass
     */
    public function addDelayedPassCanReceiveTheTypeAndPriority(): void
    {
        $config = new ContainerConfiguration();
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b'], PassConfig::TYPE_AFTER_REMOVING, 1);

        $expected = new ContainerConfiguration(
            [],
            [[[ParameterBag::class, ['a' => 'b']], PassConfig::TYPE_AFTER_REMOVING, 1]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPackage
     */
    public function addPackageShouldAppendThePackageConfigurationToTheList(): void
    {
        $package = get_class($this->createMock(Package::class));
        $config  = new ContainerConfiguration();
        $config->addPackage($package, ['a' => 'b']);

        $expected = new ContainerConfiguration(
            [],
            [],
            [],
            [[$package, ['a' => 'b']]]
        );

        self::assertEquals($expected, $config);
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

        $createdPackages = $config->getPackages();

        self::assertSame($createdPackages, $config->getPackages());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPaths
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPathsShouldReturnThePathsList(): void
    {
        $config = new ContainerConfiguration([], [], ['config']);

        self::assertEquals(['config'], $config->getPaths());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::addPath
     */
    public function addPathShouldAppendANewPathToTheList(): void
    {
        $config = new ContainerConfiguration();
        $config->addPath('services');

        $expected = new ContainerConfiguration(
            [],
            [],
            ['services']
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::setBaseClass
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getBaseClass
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function setBaseClassShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration();
        $config->setBaseClass('Test');

        self::assertSame('Test', $config->getBaseClass());
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
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpDir
     */
    public function setDumpDirShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration();
        $config->setDumpDir('/test/');

        self::assertEquals('/test', $config->getDumpDir());
    }

    /**
     * @test
     *
     * @covers \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getClassName
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getClassNameShouldCreateAHashFromPackagesFilesAndPaths(): void
    {
        $config = new ContainerConfiguration(['services.xml'], [], ['config'], [[FileListProvider::class, []]]);

        self::assertEquals(
            'Container' . md5(implode(';', ['services.xml', 'config', FileListProvider::class])),
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
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Container' . md5('') . '.php',
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
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_Container' . md5('') . '.php',
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
        $options = [
            'class'        => 'Container' . md5(''),
            'hot_path_tag' => 'container.hot_path',
        ];

        self::assertSame($options, $config->getDumpOptions());
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

        $options = [
            'class'        => 'Container' . md5(''),
            'base_class'   => 'Test',
            'hot_path_tag' => 'container.hot_path',
        ];

        self::assertSame($options, $config->getDumpOptions());
    }
}
