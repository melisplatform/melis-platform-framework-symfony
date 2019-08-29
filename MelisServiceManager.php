<?php

namespace MelisPlatformFrameworkSymfony;

use Zend\Mvc\Application;
use Zend\Session\Container;

/**
 * This class is the gateway in order for
 * Symfony to make a connection to Melis Platform
 * specially on accessing Melis Platform Services
 *
 * Class MelisServiceManager
 * @package MelisPlatformFrameworkSymfony
 */
class MelisServiceManager
{
    /**
     * Get Melis Platform Service
     *
     * @param $serviceName
     * @return array|object
     * @throws \Exception
     */
    public function getService($serviceName)
    {
        try{
            return $this->getZendServiceManager()->get($serviceName);
        }catch (\Exception $ex){
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Get Melis Platform Back office
     * language locale
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
     * Get zend service manager
     * that holds all the zend services
     *
     * @throws \Exception
     */
    protected function getZendServiceManager()
    {
        // get melisplatform app config
        $applicationConfig = $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";
        // melis module load
        $moduleLoad = $_SERVER['DOCUMENT_ROOT'] . "/../config/melis.module.load.php";
        //check if file exist
        if(!file_exists($applicationConfig))
            throw new \Exception("Zend application config missing");

        if(!file_exists($moduleLoad))
            throw new \Exception("Melis module load file missing");

        //get the application config content
        $configuration = include $applicationConfig;
        //get module load content
        $melisModuleLoad = include $moduleLoad;
        // merge modules in front and back office
        $configuration['modules'] = array_unique(array_merge($configuration['modules'], $melisModuleLoad));
        // get the zend application
        $zendApplication = Application::init($configuration);
        //return zend service manager
        return $zendApplication->getServiceManager();
    }
}