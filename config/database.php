<?php
return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'framework',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => $_ENV['DB_DATABASE'] ?? STORAGE_PATH . '/database.sqlite',
            'prefix' => '',
        ],
    ],
    
    'migrations' => [
        'table' => 'migrations',
        'path' => APP_PATH . '/Database/Migrations',
    ],
];