<?php
namespace Lcobucci\DependencyInjection\Config;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
interface Handler
{
    /**
     * @param ContainerBuilder $builder
     */
    public function __invoke(ContainerBuilder $builder);
}
