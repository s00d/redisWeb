<?php
return [
    "settings" => [
        "displayErrorDetails" => env("DEBUG", true), // set to false in production
        "addContentLengthHeader" => false, // Allow the web server to send the content-length header
        "debug"         => env("DEBUG", true),
        "whoops.editor" => "sublime",

        "isp_allow" => ["127.0.0.1"],
        "users" => [
            env("USERNAME", "root") => env("PASSWORD", "password"),
            "new" => "$2y$10$5kwuI/z6NrnoM0NUjQw3n.M77s4Hr61NjOmHsOn65EunjFTWgbaV6"
        ],

        // Renderer settings
        "renderer" => [
            "template_path" => __DIR__ . "../templates/",
            "blade_template_path" => "../templates/views", // String or array of multiple paths
            "blade_cache_path"    => "../templates/cache", // Mandatory by default, though could probably turn caching off for development
        ],

        // Monolog settings
        "logger" => [
            "name" => "redis-web",
            "path" => isset($_ENV["docker"]) ? "php://stdout" : __DIR__ . "/../logs/app.log",
            "level" => env("LOG_LEVEL", \Monolog\Logger::DEBUG),
        ],
    ],
    "data" => [
        "name"=> "Redis Web",

        "maxkeylen" => env("MAXLEYLEN", 1000),
        "seperator" => env("SEPERATOR", ":"),
        "filter"=> env("FILTER", "*"),

        "serialization"=> [],
        "count_per_page_list"=> env("COUNTPERPAGELIST", 30),

        "count_elements_page"=> env("COUNTELEMENTPAGE", false),
        "show_all_item"=> env("SHOWALLITEM", false),

        "redis"=> [
        "default"=> [
            "scheme"=> "tcp",
                "host"=> "127.0.0.1",
                "password"=> null,
                "port"=> 6379,
                "database"=> 0
            ],
            "cache"=> [
                "host"=> "127.0.0.1",
                "password"=> null,
                "port"=> 6379,
                "database"=> 15
            ]
        ],
    ]
];
