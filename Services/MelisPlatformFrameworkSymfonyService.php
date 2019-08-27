<?php

namespace MelisPlatformFrameworkSymfony\Services;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;

/**
 * This service is the gateway in order for
 * Symfony to make a connection to Melis Platform
 * specially on accessing Melis Platform Services
 *
 * Class MelisPlatformFrameworkSymfonyService
 * @package MelisPlatformFrameworkSymfony\Services
 */
class MelisPlatformFrameworkSymfonyService
{
    /**
     * Get Melis Platform Service
     *
     * @param $serviceName
     * @return array|object
     */
    public function getService($serviceName)
    {
        return $this->getServiceManager()->get($serviceName);
    }

    /**
     * Get Melis Platform lang locale
     *
     * @return mixed
     */
    public function getMelisLangLocale()
    {
        $melisLocale = '';
        $container = new Container('meliscore');
        if(!empty($container['melis-lang-locale'])){
            /**
             * Since melis locale has this format "en_EN" for example.
             * we need to explode it to get the locale format for symfony
             */
            $locale = explode('_', $container['melis-lang-locale']);
            $melisLocale = $locale[0];
        }
        return $melisLocale;
    }

    /**
     * Return Zend Service Manager that holds all
     * the registered Melis Platform Services
     *
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        // get melisplatform app config
        $configuration = include $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";
        // melis module load
        $melisModuleLoad = include $_SERVER['DOCUMENT_ROOT'] . "/../config/melis.module.load.php";
        // merge modules in front and back office
        $configuration['modules'] = array_unique(array_merge($configuration['modules'], $melisModuleLoad));
        // check for service manager config
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        // get zend servicemanagers
        $serviceManager = new ServiceManager(new ServiceManagerConfig($smConfig));
        // set service application config
        $serviceManager->setService('ApplicationConfig', $configuration);
        // load melis modules
        $serviceManager->get('ModuleManager')->loadModules();

        return $serviceManager;
    }

}