<?php

namespace MelisPlatformFrameworkSymfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MelisPlatformFrameworkSymfonyCompiler implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        /**
         * set the melis_platform.service_manager to public
         */
        if($container->has('melis_platform.service_manager')){
            $container->getDefinition('melis_platform.service_manager')->setPublic(true);
        }
        /**
         * set the melis_platform_framework.symfony_service to public
         */
        if($container->has('melis_platform_framework.symfony_service')){
            $container->getDefinition('melis_platform_framework.symfony_service')->setPublic(true);
        }
    }
}