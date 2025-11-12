<?php
return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=apiphp;charset=utf8mb4',
        'user' => 'root',
        'pass' => ''
    ],
    'app' => [
        'env' => 'local',
        'debug' => true,
        'base_url' => 'http://localhost/api-php-native-heskiamallisa/public',
        'jwt_secret' => 'nF7kQ3xTzL0vM2cR8bW9sD4pH6jY1tG5>=32_chars', 
        'allowed_origins' => ['http://localhost:3000', 'http://localhost']
    ]
];
