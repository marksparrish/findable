<?php

// findable config file
// 'your-package/config/findable.php'

return [
    'scheme' => env('ELASTIC_SCHEME', 'http'),
    'host' => env('ELASTIC_HOST', 'localhost'),
    'port' => env('ELASTIC_PORT', 9200),
    'user' => env('ELASTIC_USER', ''),
    'password' => env('ELASTIC_PASSWORD', ''),
    'ca' => env('ELASTIC_CA', null),
];
