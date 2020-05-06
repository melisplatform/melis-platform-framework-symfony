<?php

namespace MelisPlatformFrameworkSymfony\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Connection;

/**
 * Class DatabaseSwitcherListener
 * @package MelisPlatformFrameworkSymfony\EventListener
 */
class DatabaseSwitcherListener
{
    private $connection;
    private $container;


    /**
     * DatabaseSwitcherListener constructor.
     * @param Connection $connection
     * @param ContainerInterface $container
     */
    public function __construct(Connection $connection, ContainerInterface $container)
    {
        $this->connection = $connection;
        $this->container = $container;
    }


    /**
     * Change the default symfony database
     * to melis platform database
     * when this event triggered.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onKernelRequest()
    {
        try {
            /**
             * Get the melis platform database connection
             * to automatically override the symfony default
             * connection
             */
            $melisService = $this->container->get('melis_platform.service_manager');
            //melis db config resides inside config service of melis
            $melisConfig = $melisService->getService('config');

            if (!empty($melisConfig['db'])) {
                /**
                 * Process the Melis Platform db connection
                 * to apply it on symfony
                 */
                $melisDb = $melisConfig['db'];
                /**
                 * get connection params
                 */
                $conParams = $this->connection->getParams();
                if($conParams['dbname'] != $melisDb['database'] && !empty($melisDb['database'])) {
                    /**
                     * Prepare the new connection parameters
                     */
                    $conParams['dbname'] = $melisDb['database'];
                    $conParams['charset'] = $melisDb['charset'];
                    $conParams['host'] = $melisDb['hostname'];
                    $conParams['user'] = $melisDb['username'];
                    $conParams['password'] = $melisDb['password'];
                    /**
                     * close the connection if it's
                     * still connected
                     */
                    if ($this->connection->isConnected()) {
                        $this->connection->close();
                    }
                    /**
                     * Make new connection
                     */
                    $this->connection->__construct(
                        $conParams,
                        $this->connection->getDriver(),
                        $this->connection->getConfiguration(),
                        $this->connection->getEventManager()
                    );
                    $this->connection->connect();
                }
            }
        }catch (\Exception $ex) {
            exit($ex->getMessage());
        }
    }
}