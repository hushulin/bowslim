<?php
// Container
$container = $app->getContainer();

// Monolog
$container['logger'] = function ($container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

// Blade template
$container['blade'] = function ($container) {
    $settings = $container->get('settings')['blade'];
    return new Bow\view\Blade($settings['template_path'] , $settings);
};

// Service factory for the orm
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

// Redis
$container['redis'] = function ($container) {

    $redis = new Redis;

    $settings = $container->get('settings')['redis'];

    $redis->connect($settings['host'] , $settings['port']);

    if ($password = $settings['password']) {
        $redis->auth($password);
    }

    return $redis;
};

// Cache system
$container['cache'] = function ($container) {

    $settings = $container->get('settings')['cache'];

    $driver = $settings['driver'] ?: 'FilesystemCache';

    $class = "\Doctrine\Common\Cache\\$driver";

    switch ($driver) {
        case 'FilesystemCache':

            $cache_path = $settings['cache_path'] ?: sys_get_temp_dir();

            return new $class($cache_path);

            break;

        case 'RedisCache':

            $redis = $container->get('redis');

            $cache = new $class();

            $cache->setRedis($redis);

            return $cache;

            break;

        default:

            throw new Exception("Error cache driver", 1001);

            break;
    }

};


// Encryption system
$container['encryption'] = function($container){
    $config = $container->get('settings')['encryption'];

    if (Illuminate\Support\Str::startsWith($key = $config['key'], 'base64:')) {
        $key = base64_decode(substr($key, 7));
    }

    return new Illuminate\Encryption\Encrypter($key, $config['cipher']);

};
