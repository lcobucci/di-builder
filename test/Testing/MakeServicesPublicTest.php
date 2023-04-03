<?php
declare(strict_types=1);

namespace Lcobucci\DependencyInjection\Testing;

use PHPUnit\Framework\Attributes as PHPUnit;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[PHPUnit\CoversClass(MakeServicesPublic::class)]
final class MakeServicesPublicTest extends TestCase
{
    #[PHPUnit\Test]
    public function processShouldMakeAllDefinedServicesPublic(): void
    {
        $service1 = new Definition('resource');

        $service2 = new Definition('resource');
        $service2->setPublic(false);

        $builder = new ContainerBuilder();
        $builder->setDefinition('one', $service1);
        $builder->setDefinition('two', $service2);

        $pass = new MakeServicesPublic();
        $pass->process($builder);

        self::assertTrue($service1->isPublic());
        self::assertTrue($service2->isPublic());
    }

    #[PHPUnit\Test]
    public function processShouldMakeAllDefinedAliasesPublic(): void
    {
        $service = new Definition('resource');
        $alias   = new Alias('one', false);

        $builder = new ContainerBuilder();
        $builder->setDefinition('one', $service);
        $builder->setAlias('two', $alias);

        $pass = new MakeServicesPublic();
        $pass->process($builder);

        self::assertTrue($service->isPublic());
        self::assertTrue($alias->isPublic());
    }
}
