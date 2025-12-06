<?php
return [
    'app' => [
        'name' => 'Mini Framework',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone' => 'Asia/Tehran',
    ],
    
    'logger' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'syslog' => $_ENV['LOG_SYSLOG'] ?? false,
        'debug' => $_ENV['APP_DEBUG'] ?? false,
    ],
    
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'lifetime' => $_ENV['CACHE_LIFETIME'] ?? 3600,
    ],
    
    'security' => [
        'bcrypt_cost' => 12,
        'csrf_token_lifetime' => 3600,
    ],
    
    'view' => [
        'cache' => $_ENV['VIEW_CACHE'] ?? true,
        'debug' => $_ENV['APP_DEBUG'] ?? false,
    ],
];