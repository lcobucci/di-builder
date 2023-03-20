<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Config;

use Generator;
use Lcobucci\DependencyInjection\Compiler\ParameterBag;
use Lcobucci\DependencyInjection\CompilerPassListProvider;
use Lcobucci\DependencyInjection\FileListProvider;
use Lcobucci\DependencyInjection\Testing\MakeServicesPublic;
use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

use function iterator_to_array;
use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

#[PHPUnit\CoversClass(ContainerConfiguration::class)]
final class ContainerConfigurationTest extends TestCase
{
    private CompilerPassInterface&MockObject $pass;

    #[PHPUnit\Before]
    public function configureDependencies(): void
    {
        $this->pass = $this->createMock(CompilerPassInterface::class);
    }

    #[PHPUnit\Test]
    public function getFilesShouldReturnTheFileList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp', ['services.xml']);

        self::assertSame(['services.xml'], iterator_to_array($config->getFiles()));
    }

    #[PHPUnit\Test]
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
            [[$package1::class, []], [$package2::class, []]],
        );

        self::assertSame(['services2.xml', 'services.xml'], iterator_to_array($config->getFiles(), false));
    }

    #[PHPUnit\Test]
    public function addFileShouldAppendANewFileToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addFile('services.xml');

        self::assertEquals(new ContainerConfiguration('Me\\MyApp', ['services.xml']), $config);
    }

    #[PHPUnit\Test]
    public function getPassListShouldReturnTheHandlersList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp', [], [[$this->pass, 'beforeOptimization']]);

        self::assertSame([[$this->pass, 'beforeOptimization']], iterator_to_array($config->getPassList()));
    }

    #[PHPUnit\Test]
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
            [[$package1::class, []], [$package2::class, []]],
        );

        self::assertEquals(
            [
                [new MakeServicesPublic(), 'beforeOptimization'],
                [$this->pass, 'beforeOptimization'],
            ],
            iterator_to_array($config->getPassList(), false),
        );
    }

    #[PHPUnit\Test]
    public function addPassShouldAppendANewHandlerToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addPass($this->pass);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[$this->pass, PassConfig::TYPE_BEFORE_OPTIMIZATION, 0]],
        );

        self::assertEquals($expected, $config);
    }

    #[PHPUnit\Test]
    public function addPassCanReceiveTheTypeAndPriority(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addPass($this->pass, PassConfig::TYPE_AFTER_REMOVING, 1);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[$this->pass, PassConfig::TYPE_AFTER_REMOVING, 1]],
        );

        self::assertEquals($expected, $config);
    }

    #[PHPUnit\Test]
    public function addDelayedPassShouldAppendANewCompilerPassToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b']);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[[ParameterBag::class, ['a' => 'b']], PassConfig::TYPE_BEFORE_OPTIMIZATION, 0]],
        );

        self::assertEquals($expected, $config);
    }

    #[PHPUnit\Test]
    public function addDelayedPassCanReceiveTheTypeAndPriority(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addDelayedPass(ParameterBag::class, ['a' => 'b'], PassConfig::TYPE_AFTER_REMOVING, 1);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [[[ParameterBag::class, ['a' => 'b']], PassConfig::TYPE_AFTER_REMOVING, 1]],
        );

        self::assertEquals($expected, $config);
    }

    #[PHPUnit\Test]
    public function addPackageShouldAppendThePackageConfigurationToTheList(): void
    {
        $package = $this->createMock(Package::class)::class;
        $config  = new ContainerConfiguration('Me\\MyApp');
        $config->addPackage($package, ['a' => 'b']);

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [],
            [],
            [[$package, ['a' => 'b']]],
        );

        self::assertEquals($expected, $config);
    }

    #[PHPUnit\Test]
    public function getPackagesShouldReturnAListOfInstantiatedPackages(): void
    {
        $package = $this->createMock(Package::class);
        $config  = new ContainerConfiguration('Me\\MyApp', [], [], [], [[$package::class, []]]);

        self::assertEquals([$package], $config->getPackages());
    }

    #[PHPUnit\Test]
    public function getPackagesShouldInstantiateThePackagesOnlyOnce(): void
    {
        $packageName = $this->createMock(Package::class)::class;
        $config      = new ContainerConfiguration('Me\\MyApp', [], [], [], [[$packageName, []]]);

        $createdPackages = $config->getPackages();

        self::assertSame($createdPackages, $config->getPackages());
    }

    #[PHPUnit\Test]
    public function getPathsShouldReturnThePathsList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp', [], [], ['config']);

        self::assertEquals(['config'], $config->getPaths());
    }

    #[PHPUnit\Test]
    public function addPathShouldAppendANewPathToTheList(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->addPath('services');

        $expected = new ContainerConfiguration(
            'Me\\MyApp',
            [],
            [],
            ['services'],
        );

        self::assertEquals($expected, $config);
    }

    #[PHPUnit\Test]
    public function setBaseClassShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->setBaseClass('Test');

        self::assertSame('Test', $config->getBaseClass());
    }

    #[PHPUnit\Test]
    public function getDumpDirShouldReturnTheAttributeValue(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');

        self::assertEquals(sys_get_temp_dir(), $config->getDumpDir());
    }

    #[PHPUnit\Test]
    public function setDumpDirShouldChangeTheAttribute(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->setDumpDir('/test/');

        self::assertEquals('/test', $config->getDumpDir());
    }

    #[PHPUnit\Test]
    public function getDumpFileShouldReturnTheFullPathOfDumpFile(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');

        self::assertEquals(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'me_myapp' . DIRECTORY_SEPARATOR . 'AppContainer.php',
            $config->getDumpFile(),
        );
    }

    #[PHPUnit\Test]
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

    #[PHPUnit\Test]
    public function getDumpOptionsShouldIncludeBaseWhenWasConfigured(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $config->setBaseClass('Test');

        $options = [
            'class'        => ContainerConfiguration::CLASS_NAME,
            'namespace'    => 'Me\\MyApp',
            'base_class'   => 'Test',
            'hot_path_tag' => 'container.hot_path',
        ];

        self::assertSame($options, $config->getDumpOptions());
    }

    #[PHPUnit\Test]
    public function withAddedNamespaceShouldModifyTheNamespaceOfANewInstanceOnly(): void
    {
        $config = new ContainerConfiguration('Me\\MyApp');
        $other  = $config->withSubNamespace('\\Testing');

        self::assertSame('Me\\MyApp\\AppContainer', $config->getClassName());
        self::assertSame('Me\\MyApp\\Testing\\AppContainer', $other->getClassName());
    }
}
