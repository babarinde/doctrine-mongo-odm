# DoctrineMongoODM Component

[![Build Status](https://travis-ci.org/helderjs/doctrine-mongo-odm.svg?branch=master)](https://travis-ci.org/helderjs/doctrine-mongo-odm)

It's a component based on [DoctrineMongoODMModule](https://github.com/doctrine/DoctrineMongoODMModule) that provides [DoctrineMongoDbODM](http://docs.doctrine-project.org/projects/doctrine-mongodb-odm) integration for
several (Micro-)frameworks. The goal is be light and easy to configure, for that the library rely just on the [PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md) and [MongoBD ODM ^2.0](http://docs.doctrine-project.org/projects/doctrine-mongodb-odm/en/latest/). 

## Requirements

- PHP 7.2+
- ext-mongodb

We recommend using a dependency injection container, and typehint against [PSR-11](https://github.com/php-fig/container).

## Installation

Install this library using composer:
In composer.json
```
"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/babarinde/doctrine-mongo-odm"
        }
    ]
```
```bash
$ composer require helderjs/doctrine-mongo-odm:dev-mongo-odm-2.0
```

## Configuration

How to create the config file (What you should/can put in the config).

```php
return [
    'config' => [
    'doctrine' => [
        'default' => 'odm_default',
        'connection' => [
            'odm_default' => [
                'server'           => 'expressiveDB',
                'port'             => '27017',
                'user'             => 'testUser',
                'password'         => 'testPass',
                'dbname'           => 'testDB',
                'options'          => [],
            ],
            // 'odm_secondary' => [
            //     'connectionString' => 'mongodb://username:password@server2:27017/mydb',
            //     'options'          => []
            // ],
        ],
        'configuration' => [
            'odm_default' => [
                'metadata_cache'     => ArrayCache::class, // optional
                'driver'             => MappingDriver::class,
                'generate_proxies'   => Configuration::AUTOGENERATE_FILE_NOT_EXISTS,
                'proxy_dir'          => 'data/DoctrineMongoODMModule/Proxy',
                'proxy_namespace'    => 'DoctrineMongoODMModule\Proxy',
                'generate_hydrators' => Configuration::AUTOGENERATE_FILE_NOT_EXISTS,
                'hydrator_dir'       => 'data/DoctrineMongoODMModule/Hydrator',
                'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
                'default_db'         => 'testDB',
                // 'filters'            => [], // custom filters (optional)
                // 'types'              => [], // custom types (optional)
                // 'metadata_factory_name' => 'stdClass' \\ optional
            ]
        ],
        'documentmanager' => [
            'odm_default' => [
                'connection'    => \MongoDB\Client::class,
                'configuration' => \Doctrine\ODM\MongoDB\Configuration::class,
                // 'eventmanager'  => \Doctrine\ODM\MongoDB\EventManager::class, \\ optional
            ],
            // 'odm_secondary' => [
            //     'connection'    => 'doctrine.connection.secondary',
            //     'configuration' => \Doctrine\ODM\MongoDB\Configuration::class,
            //     'eventmanager'  => 'doctrine.eventmanager.secondary', \\ optional
            // ]
        ],
        'driver' => [
            'odm_default' => [
                AnnotationDriver::class => [
                    'documents_dir' => [
                         './src/MyApp/Documents' // not sure if this is still necessary, works without it though
                    ]
                ]
            ]
        ]
    ]
],
];
```

Configuring DI at Mezzio
```php
...
'dependencies' => [
    'invokables' => [
        \Doctrine\Common\Cache\ArrayCache::class => \Doctrine\Common\Cache\ArrayCache::class,
        \MyLogger::class  => \MyLogger::class,
    ],
    'factories' => [
        \Doctrine\ODM\MongoDB\Configuration::class   => ConfigurationFactory::class,
        \MongoDB\Client::class      => ConnectionFactory::class,
        \Doctrine\ODM\MongoDB\EventManager::class    => EventManagerFactory::class,
        \Doctrine\ODM\MongoDB\DocumentManager::class => DocumentManagerFactory::class,
        // 'doctrine.connection.secondary'              => new ConnectionFactory('odm_secondary'),
        // 'doctrine.eventmanager.secondary'            => new EventManagerFactory('odm_secondary'),
        // 'doctrine.documentmandager.secondary'        => new DocumentManagerFactory('odm_secondary'),
        // \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::class   => \Helderjs\Component\DoctrineMongoODM\AnnotationDriverFactory::class,
        // \Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver::class          => \Helderjs\Component\DoctrineMongoODM\AnnotationDriverFactory::class,
        // \Doctrine\ODM\MongoDB\Mapping\Driver\YamlDriver::class         => \Helderjs\Component\DoctrineMongoODM\AnnotationDriverFactory::class,
        // \Doctrine\ODM\MongoDB\Mapping\Driver\MappingDriverChain::class => \Helderjs\Component\DoctrineMongoODM\AnnotationDriverFactory::class,
    ],
 ],
 ...
 Register document namespace in Composer
 "autoload": {
        "psr-4": {
            "MyApp\\Document\\": "src/MyApp/Document"
        }
    }
Bootstrap in public/index.php:
change
require 'vendor/autoload.php';
to
$loader = require 'vendor/autoload.php';
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader,'loadClass']);
```

SlimPHP

```php
$container['doctrine-connection'] = function ($container) {
    $factory = new ConnectionFactory();
    
    return $factory($container);
};

$container['doctrine-configuration'] = function ($container) {
    $factory = new ConfigurationFactory();
    
    return $factory($container);
};

$container['doctrine-eventmanager'] = function ($container) {
    $factory = new EventManagerFactory();
    
    return $factory($container);
};

$container['doctrine-driver'] = function ($container) {
    $factory = new MappingDriverChainFactory();
    
    return $factory($container);
};
```

## Goals

- Improve unit tests
- Improve documentation
- Implementing real examples
- ?introduce new features?

If you want to help: install, test it, report an issue, fork, open a pull request 

## License

The MIT License (MIT). Please see [License File](https://github.com/helderjs/doctrine-mongo-odm/blob/master/LICENSE) for more information.
