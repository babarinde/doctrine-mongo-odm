<?php
/**
 * DoctrineMongoODM Component
 *
 * @see       https://github.com/helderjs/doctrine-mongo-odm
 * @copyright @copyright Copyright (c) 2016 Helder Santana
 * @license   https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE MIT License
 */
namespace Helderjs\Component\DoctrineMongoODM;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Helderjs\Component\DoctrineMongoODM\Exception\InvalidConfigException;
use MongoDB\Client;
use Psr\Container\ContainerInterface;

/**
 * Class ConnectionFactory
 *
 * @package Helderjs\Component\DoctrineMongoODM
 */
class ConnectionFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @return Connection
     * @throws InvalidConfigException for invalid config service values.
     */
    public function __invoke(ContainerInterface $container)
    {
        try {
            $options = $this->getDoctrineConfiguration($container, 'connection');

            if (empty($options)) {
                return new Client('mongodb://127.0.0.1', [], ['typeMap' => DocumentManager::CLIENT_TYPEMAP]);
            }

            $connectionString = isset($options['connection_string'])
                ? $options['connection_string'] : null;
            $dbName = null;
            if (empty($connectionString)) {
                $connectionString = 'mongodb://';
                $user = $options['user'];
                $password = $options['password'];
                $dbName = $options['dbname'];
                if ($user && $password) {
                    $connectionString .= $user . ':' . $password . '@';
                }
                $connectionString .= $options['server'] . ':' . $options['port'];
                if ($dbName) {
                    $connectionString .= '/' . $dbName;
                }
            } else {
                // parse dbName from the connectionString
                $dbStart = strpos($connectionString, '/', 11);
                if (false !== $dbStart) {
                    $dbEnd = strpos($connectionString, '?');
                    $dbName = substr(
                        $connectionString,
                        $dbStart + 1,
                        $dbEnd ? ($dbEnd - $dbStart - 1) : PHP_INT_MAX
                    );
                }
            }

            return new Client($connectionString, $options['options'], ['typeMap' => DocumentManager::CLIENT_TYPEMAP]);
        } catch (\Exception $e) {
            throw new InvalidConfigException($e->getMessage());
        }
    }
}
