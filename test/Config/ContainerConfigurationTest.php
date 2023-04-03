<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Generator;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Lcobucci\DependencyInjection\FileListProvider;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

use function get_class;
use function iterator_to_array;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

/** @coversDefaultClass \Lcobucci\DependencyInjection\Config\ContainerConfiguration */
final class ContainerConfigurationTest extends TestCase
{
    /** @var CompilerPassInterface&MockObject */
    private CompilerPassInterface $pass;

    /** @before */
    public function configureDependencies(): void
    {
        $this->pass = $this->createMock(CompilerPassInterface::class);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::getFiles
     * @covers ::filterPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     */
    public function getFilesShouldReturnTheFileList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp', ['services.xml']);

        self::assertSame(['services.xml'], iterator_to_array($config->getFiles()));
    }

    /**
     * @test
     *
     * @covers ::getFiles
     * @covers ::filterPackages
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
                yield [new MakeServicesPublic(), 'beforeOptimization'];
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
            'Me\\MyApp',
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
     * @covers ::__construct
     * @covers ::addFile
     */
    public function addFileShouldAppendANewFileToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addFile('services.xml');

        self::assertEquals(new ContainerConfiguration('Me\\MyApp', ['services.xml']), $config);
    }

    /**
     * @test
     *
     * @covers ::getPassList
     * @covers ::filterPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getPackages
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPassListShouldReturnTheHandlersList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp', [], [[$this->pass, 'beforeOptimization']]);

        self::assertSame([[$this->pass, 'beforeOptimization']], iterator_to_array($config->getPassList()));
    }

    /**
     * @test
     *
     * @covers ::getPassList
     * @covers ::filterPackages
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
                yield [new MakeServicesPublic(), 'beforeOptimization'];
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
            'Me\\MyApp',
            [],
            [[$this->pass, 'beforeOptimization']],
            [],
            [[get_class($package1), []], [get_class($package2), []]]
        );

        self::assertEquals(
            [
                [new MakeServicesPublic(), 'beforeOptimization'],
                [$this->pass, 'beforeOptimization'],
            ],
            iterator_to_array($config->getPassList(), false)
        );
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::addPass
     */
    public function addPassShouldAppendANewHandlerToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addPass($this->pass);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[$this->pass, PassConfig::TYPE_BEFORE_OPTIMIZATION, 0]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::addPass
     */
    public function addPassCanReceiveTheTypeAndPriority(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addPass($this->pass, PassConfig::TYPE_AFTER_REMOVING, 1);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[$this->pass, PassConfig::TYPE_AFTER_REMOVING, 1]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::addDelayedPass
     */
    public function addDelayedPassShouldAppendANewCompilerPassToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b']);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[[ParameterBag::class, ['a' => 'b']], PassConfig::TYPE_BEFORE_OPTIMIZATION, 0]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::addDelayedPass
     */
    public function addDelayedPassCanReceiveTheTypeAndPriority(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b'], PassConfig::TYPE_AFTER_REMOVING, 1);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[[ParameterBag::class, ['a' => 'b']], PassConfig::TYPE_AFTER_REMOVING, 1]]
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::addPackage
     */
    public function addPackageShouldAppendThePackageConfigurationToTheList(): void
    {
        $package = get_class($this->createMock(Package::class));
        $config  = new ContainerConfiguration('Me\\MyApp');
        $config->addPackage($package, ['a' => 'b']);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
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
     * @covers ::getPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPackagesShouldReturnAListOfInstantiatedPackages(): void
    {
        $package = $this->createMock(Package::class);
        $config  = new ContainerConfiguration('Me\\MyApp', [], [], [], [[get_class($package), []]]);

        self::assertEquals([$package], $config->getPackages());
    }

    /**
     * @test
     *
     * @covers ::getPackages
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPackagesShouldInstantiateThePackagesOnlyOnce(): void
    {
        $packageName = get_class($this->createMock(Package::class));
        $config      = new ContainerConfiguration('Me\\MyApp', [], [], [], [[$packageName, []]]);

        $createdPackages = $config->getPackages();

        self::assertSame($createdPackages, $config->getPackages());
    }

    /**
     * @test
     *
     * @covers ::getPaths
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getPathsShouldReturnThePathsList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp', [], [], ['config']);

        self::assertEquals(['config'], $config->getPaths());
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::addPath
     */
    public function addPathShouldAppendANewPathToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addPath('services');

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [],
            ['services']
        );

        self::assertEquals($expected, $config);
    }

    /**
     * @test
     *
     * @covers ::setBaseClass
     * @covers ::getBaseClass
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function setBaseClassShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->setBaseClass('Test');

        self::assertSame('\\Test', $config->getBaseClass());
    }

    /**
     * @test
     *
     * @covers ::getDumpDir
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getDumpDirShouldReturnTheAttributeValue(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');

        self::assertEquals(sys_get_temp_dir(), $config->getDumpDir());
    }

    /**
     * @test
     *
     * @covers ::setDumpDir
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::getDumpDir
     */
    public function setDumpDirShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->setDumpDir('/test/');

        self::assertEquals('/test', $config->getDumpDir());
    }

    /**
     * @test
     *
     * @covers ::getDumpFile
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getDumpFileShouldReturnTheFullPathOfDumpFile(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');

        self::assertEquals(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'me_myapp' . DIRECTORY_SEPARATOR . 'AppContainer.php',
            $config->getDumpFile()
        );
    }

    /**
     * @test
     *
     * @covers ::getDumpOptions
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getDumpOptionsShouldReturnTheDumpingInformation(): void
    {
        $config  = new ContainerConfiguration('Me\\MyApp');
        $options = [
            'class'        => ContainerConfiguration::CLASS_NAME,
            'namespace'    => 'Me\\MyApp',
            'hot_path_tag' => 'container.hot_path',
        ];

        self::assertSame($options, $config->getDumpOptions());
    }

    /**
     * @test
     *
     * @covers ::setBaseClass
     * @covers ::getDumpOptions
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function getDumpOptionsShouldIncludeBaseWhenWasConfigured(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->setBaseClass('Test');

        $options = [
            'class'        => ContainerConfiguration::CLASS_NAME,
            'namespace'    => 'Me\\MyApp',
            'base_class'   => '\\Test',
            'hot_path_tag' => 'container.hot_path',
        ];

        self::assertSame($options, $config->getDumpOptions());
    }

    /**
     * @test
     *
     * @covers ::withSubNamespace
     * @covers ::getClassName
     *
     * @uses \Lcobucci\DependencyInjection\Config\ContainerConfiguration::__construct
     */
    public function withAddedNamespaceShouldModifyTheNamespaceOfANewInstanceOnly(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $other  = $config->withSubNamespace('\\Testing');

        self::assertSame('Me\\MyApp\\AppContainer', $config->getClassName());
        self::assertSame('Me\\MyApp\\Testing\\AppContainer', $other->getClassName());
    }
}
