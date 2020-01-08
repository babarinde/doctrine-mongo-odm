<?php
/**
 * DoctrineMongoODM Component
 *
 * @see       https://github.com/helderjs/doctrine-mongo-odm
 * @copyright @copyright Copyright (c) 2016 Helder Santana
 * @license   https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE MIT License
 */
namespace Helderjs\Test\Component\DoctrineMongoODM;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory;
use Doctrine\ODM\MongoDB\Repository\DefaultRepositoryFactory;
use Doctrine\ODM\MongoDB\Repository\RepositoryFactory;
use Helderjs\Component\DoctrineMongoODM\DocumentManagerFactory;
use Helderjs\Component\DoctrineMongoODM\Exception\InvalidConfigException;
use MongoDB\Client;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use Psr\Container\ContainerInterface;

class DocumentManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testCallingFactoryWithNoConfigReturns()
    {
        $factory = new DocumentManagerFactory();

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());

        $this->container->has('doctrine')->willReturn(true);
        $this->container->get('doctrine')->willReturn([]);
        $this->container->has('config')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->expectException(InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testCreationWithConfiguration()
    {
        $options = [
            'doctrine' => [
                'documentmanager' => [
                    'odm_default' => [
                        'connection'    => Client::class,
                        'configuration' => Configuration::class,
                        'eventmanager'  => EventManager::class,
                    ],
                ],
            ],
        ];

        $connection = $this->prophesize(Client::class);
        $configuration = $this->prophesize(Configuration::class);
        $eventManager = $this->prophesize(EventManager::class);
        $lazyLoadingGhostFactory = $this->prophesize(LazyLoadingGhostFactory::class);
        $repositoryFactory = $this->prophesize(RepositoryFactory::class);

        $configuration->getClassMetadataFactoryName()->willReturn(ClassMetadataFactory::class);
        $configuration->getMetadataCacheImpl()->willReturn(null);
        $configuration->getRepositoryFactory()->willReturn(DefaultRepositoryFactory::class);
        $configuration->getHydratorDir()->willReturn('/tmp/hydrator');
        $configuration->getHydratorNamespace()->willReturn('\Hydarator');
        $configuration->getAutoGenerateHydratorClasses()->willReturn(false);
        $configuration->getProxyDir()->willReturn('/tmp/proxy');
        $configuration->getProxyNamespace()->willReturn('\Proxy');
        $configuration->getAutoGenerateProxyClasses()->willReturn(false);
        $configuration->buildGhostObjectFactory()->willReturn($lazyLoadingGhostFactory);
        $configuration->getRepositoryFactory()->willReturn($repositoryFactory);

        $connection->getTypeMap()->willReturn(DocumentManager::CLIENT_TYPEMAP);

        $this->container->has('doctrine')->willReturn(false);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($options);
        $this->container->get(Client::class)->willReturn($connection->reveal());
        $this->container->get(Configuration::class)->willReturn($configuration->reveal());
        $this->container->get(EventManager::class)->willReturn($eventManager->reveal());

        $factory = new DocumentManagerFactory();
        $documentManager = $factory($this->container->reveal());

        $this->assertInstanceOf(DocumentManager::class, $documentManager);
    }
}
