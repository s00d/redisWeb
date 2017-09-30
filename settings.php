<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        'debug'         => true,
        'whoops.editor' => 'sublime',

        "isp_allow" => ['127.0.0.1'],
        "users" => [
            "admin" => "password",
            "new" => "$2y$10$5kwuI/z6NrnoM0NUjQw3n.M77s4Hr61NjOmHsOn65EunjFTWgbaV6"
        ],

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '../templates/',
            'blade_template_path' => '../templates/views', // String or array of multiple paths
            'blade_cache_path'    => '../templates/cache', // Mandatory by default, though could probably turn caching off for development
        ],

        // Monolog settings
        'logger' => [
            'name' => 'redis-web',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
    'data' => [
        "name"=> "Redis Web",

        "maxkeylen"=> 1000,
        "seperator"=> ":",
        "filter"=> "*",

        "serialization"=> [],
        "count_per_page"=> 30,

        "count_elements_page"=> false,
        "show_all_item"=> false,

        "redis"=> [
        "default"=> [
            "scheme"=> "tcp",
                "host"=> "127.0.0.1",
                "password"=> null,
                "port"=> 6379,
                "database"=> 0
            ],
            "publisher"=> [
                "host"=> "127.0.0.1",
                "password"=> null,
                "port"=> 6379,
                "database"=> 0
            ]
        ],
    ]
];
