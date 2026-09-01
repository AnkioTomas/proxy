<?php

return [
    'debug' => true,
    'timezone' => 'Asia/Shanghai',
    'default_route' => true,
    'domain' => [
        '0.0.0.0',
    ],
    'version' => '1.0.0',
    'ip' => '127.0.0.1',
    'port' => 10211,
    'framework_start' => [
        'nova\\plugin\\tpl\\Handler',
        'nova\\plugin\\login\\LoginManager',
    ],
    'db' => [
        'type' => 'mysql',
        'host' => '',
        'port' => 3306,
        'username' => '',
        'password' => '',
        'db' => '',
        'charset' => 'utf8mb4',
    ],
];
