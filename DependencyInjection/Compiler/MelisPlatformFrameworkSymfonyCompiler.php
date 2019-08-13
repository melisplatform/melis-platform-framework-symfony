<?php

namespace MelisPlatformFrameworkSymfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MelisPlatformFrameworkSymfonyCompiler implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if($container->has('mp_framework_symfony.service')){
            $container->getDefinition('mp_framework_symfony.service')->setPublic(true);
        }
    }
}