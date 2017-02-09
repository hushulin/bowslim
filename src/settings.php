<?php
return [
    'settings' => [

        // Global settings
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // 'determineRouteBeforeAppMiddleware' => true,
        'host' => '',//'http://user.jcweixiaoyuan.cn',

        'route.prefix' => '/user-system-v1',

        // Route cache settings
        // 'routerCacheFile' => __DIR__ . '/../logs/route.cache',

        // Template blade settings
        'blade' => [
            'template_path' => __DIR__ . '/../templates',
            'cache_path' => __DIR__ . '/../logs',
            // 'extension' => '',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'jiuchun-user',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Database settings
        'db' => [
            // 'write' => [
            //     'host' => ['192.168.1.5','192.168.1.5','192.168.1.5'],
            // ],
            // 'read' => [
            //     'host' => [
            //         '192.168.1.5',
            //         '192.168.1.5',
            //         '192.168.1.5',
            //         '192.168.1.5',
            //         '192.168.1.5',
            //         '192.168.1.5',
            //     ],
            // ],
            'driver' => 'mysql',
            'host' => '192.168.1.5',
            'database' => 'slim',
            'username' => 'root',
            'password' => '', //
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        // Old Database for migration
        'v2' => [
            'driver' => 'mysql',

            'host' => '192.168.1.105',
            'database' => 'jcwei3.1',
            'username' => 'root',
            'password' => '', //

            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => 'jcwei_',
        ],

        // Redis configure settings
        'redis' => [
            'host' => '192.168.1.170',
            'port' => 6379,
            'password' => '',
        ],

        // Using for migration system settings
        'migration' => [
            'driver'   => 'pdo_mysql',
            'user'     => 'root',
            'dbname'   => 'slim',
            'host' => '192.168.1.5',
            'database' => 'slim',
            'username' => 'root',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ],

        // Add a new migration configuration standard template
        // 'migration_example' => [
        //     'driver' => 'pdo_mysql',
        //     'user' => 'root',
        //     'password' => '',
        //     'host' => '192.168.1.5',
        //     'dbname' => 'slim',

        //     // 'port'
        //     // 'unix_socket'
        //     // 'charset'
        //     // 'collate' 存在于建表系统
        // ],

        // Cache system configure settings
        'cache' => [
            'driver' => 'RedisCache', //FilesystemCache , RedisCache
            'cache_path' => __DIR__ . '/../logs/',
        ],

        // Guard ips settings
        'enableips' => [
            '192.168.1.1',
            '192.168.1.2',
            '127.0.0.1',
            '192.168.1.170',
            '192.168.1.171',
            '192.168.1.172',
            '192.168.1.15',
            '192.168.1.5',
        ],

        // Wechat settings
        'wechat' => [
            'encodingAesKey' => '',
            'token' => '',
            'corpId' => '',
            'suiteId' => '',
            'secret' => '',
        ],

        // Mpwechat settings
        'mpwechat' => [
            'debug'  => true,
            'app_id' => '',
            'secret' => '',
            'token'  => '',
            // 'aes_key' => null, // 可选
            'log' => [
                'level' => 'debug',
                'file'  => __DIR__ . '/../logs/easywechat.log', // XXX: 绝对路径！！！！
            ],
            //...
        ],

        // Encryption settings
        'encryption' => [
            'key' => '',
            'cipher' => 'AES-256-CBC',
        ],

        /**
         * Guzzle 全局设置
         *
         * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
         */
        'guzzle' => [
            'timeout' => 20.0, // 超时时间（秒）
            // 'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
        ],
    ],
];
