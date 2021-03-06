<?php

namespace MelisPlatformFrameworkSymfony;

use Laminas\Mvc\Application;
use Laminas\Session\Container;

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
            return $this->getServiceManager()->get($serviceName);
        }catch (\Exception $ex){
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Get Melis Platform Back office / Front
     * language id
     * @return int
     */
    public function getMelisLangId()
    {
        $langId = 1;
        try {
            $uri = $_SERVER['REQUEST_URI'];
            $uri = explode('/', $uri);
            /**
             * if uri starts with melis, then
             * we get the melis back office
             * language id
             */
            if (!empty($uri[1]) && $uri[1] == 'melis') {
                $container = new Container('meliscore');
                if (!empty($container['melis-lang-id']))
                    $langId = $container['melis-lang-id'];
            } else {
                /**
                 * get the front language id
                 */
                $container = new Container('melisplugins');
                if (!empty($container['melis-plugins-lang-id']))
                    $langId = $container['melis-plugins-lang-id'];
            }
        }catch (\Exception $ex){
            $langId = 1;
        }

        return $langId;
    }

    /**
     * Get Melis Platform Back office / Front
     * language locale
     *
     * @return mixed
     */
    public function getMelisLangLocale()
    {
        $newLocale = 'en';

        try {
            $uri = $_SERVER['REQUEST_URI'];
            $uri = explode('/', $uri);
            /**
             * if uri starts with melis, then
             * we get the melis back office
             * language locale
             */
            if (!empty($uri[1]) && $uri[1] == 'melis') {
                $container = new Container('meliscore');
                if (!empty($container['melis-lang-locale']))
                    $melisLocale = $container['melis-lang-locale'];
                else
                    $melisLocale = 'en_EN';
            } else {
                /**
                 * get the front language locale
                 */
                $container = new Container('melisplugins');
                if (!empty($container['melis-plugins-lang-locale']))
                    $melisLocale = $container['melis-plugins-lang-locale'];
                else
                    $melisLocale = 'en_EN';
            }

            if (!empty($melisLocale)) {
                /**
                 * Since melis locale has this format "en_EN" for example.
                 * we need to explode it to get the locale format for symfony
                 */
                $locale = explode('_', $melisLocale);
                $newLocale = $locale[0];
            }
        }catch (\Exception $ex){
            $newLocale = 'en';
        }

        return $newLocale;
    }

    /**
     * Get Laminas service manager
     * that holds all the services
     *
     * @throws \Exception
     */
    protected function getLaminasServiceManager()
    {
        // get melisplatform app config
        $applicationConfig = $_SERVER['DOCUMENT_ROOT'] . "/../config/application.config.php";
        // melis module load
        $moduleLoad = $_SERVER['DOCUMENT_ROOT'] . "/../config/melis.module.load.php";
        //check if file exist
        if(!file_exists($applicationConfig))
            throw new \Exception("Laminas application config missing");

        if(!file_exists($moduleLoad))
            throw new \Exception("Melis module load file missing");

        //get the application config content
        $configuration = include $applicationConfig;
        //get module load content
        $melisModuleLoad = include $moduleLoad;
        // merge modules in front and back office
        $configuration['modules'] = array_unique(array_merge($configuration['modules'], $melisModuleLoad));
        // get the laminas application
        $application = Application::init($configuration);
        //return laminas service manager
        return $application;
    }

    /**
     * @return \Laminas\ServiceManager\ServiceManager
     * @throws \Exception
     */
    public function getServiceManager()
    {
        return $this->getLaminasServiceManager()->getServiceManager();
    }

    /**
     * @return \Laminas\EventManager\EventManagerInterface
     * @throws \Exception
     */
    public function getEventManager()
    {
        return $this->getLaminasServiceManager()->getEventManager();
    }

    /**
     * @return array|object
     * @throws \Exception
     */
    public function getViewHelperManager()
    {
        return $this->getService('ViewHelperManager');
    }
}