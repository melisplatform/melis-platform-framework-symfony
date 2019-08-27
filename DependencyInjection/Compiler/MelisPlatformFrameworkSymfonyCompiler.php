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
         * set the melis_platform.services to public
         */
        if($container->has('melis_platform.services')){
            $container->getDefinition('melis_platform.services')->setPublic(true);
        }
    }
}