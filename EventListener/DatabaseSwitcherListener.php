<?php

namespace MelisPlatformFrameworkSymfony\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Connection;

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
            $melisService = $this->container->get('melis_platform.services');
            $melisConfig = $melisService->getService('config');

            if (!empty($melisConfig['db'])) {
                /**
                 * Process the Melis Platform db connection
                 * to apply it on symfony
                 */
                $melisDb = $melisConfig['db'];
                $melisDbDSN = explode(';', $melisDb['dsn']);
                $dbName = '';
                $host = '';
                $charset = '';
                foreach($melisDbDSN as $val){
                    $data = explode('=', $val);
                    if(!empty($data[1])){
                        $dbName = (strpos($data[0], 'dbname') !== false && empty($dbName)) ? $data[1] : $dbName;
                        $host = (strpos($data[0], 'host') !== false && empty($host)) ? $data[1] : $host;
                        $charset = (strpos($data[0], 'charset') !== false && empty($charset)) ? $data[1] : $charset;
                    }
                }
                /**
                 * get connection params
                 */
                $conParams = $this->connection->getParams();
                if($conParams['dbname'] != $dbName && !empty($dbName)) {
                    $conParams['dbname'] = $dbName;
                    $conParams['charset'] = $charset;
                    $conParams['host'] = $host;
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

        }
    }
}