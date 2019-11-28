<?php

namespace App\Bundle\SymfonyTpl\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SymfonyTplExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
//        $configuration = new Configuration();
//        $config = $this->processConfiguration($configuration, $configs);
        /**
         * Load the bundle services
         */
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        /**
         * Make a parameter to access the table config
         */
        foreach($configs as $config){
            if(!empty($config)){
                if(!empty($config['table'])){
                    $container->setParameter('symfony_tpl_table', $config['table']['symfony_tpl_table']);
                    break;
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function prepend(ContainerBuilder $container)
    {
        /**
         * Load the config
         */
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('config.yaml');
    }
}