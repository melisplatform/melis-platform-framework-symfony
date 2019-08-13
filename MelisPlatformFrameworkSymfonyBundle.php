<?php

namespace MelisPlatformFrameworkSymfony;

use MelisPlatformFrameworkSymfony\DependencyInjection\Compiler\MelisPlatformFrameworkSymfonyCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MelisPlatformFrameworkSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MelisPlatformFrameworkSymfonyCompiler());
    }
}