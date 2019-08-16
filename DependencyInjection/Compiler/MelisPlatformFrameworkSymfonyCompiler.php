<?php

namespace MelisPlatformFrameworkSymfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MelisPlatformFrameworkSymfonyCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if($container->has('melis_platform.services')){
            $container->getDefinition('melis_platform.services')->setPublic(true);
        }
    }
}