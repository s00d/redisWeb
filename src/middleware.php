<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// Fetch DI Container
$container = $app->getContainer();

$whoopsGuard = new \Zeuxisoo\Whoops\Provider\Slim\WhoopsGuard();
$whoopsGuard->setApp($app);
$whoopsGuard->setRequest($container['request']);
$whoopsGuard->setHandlers([]);
$whoopsGuard->install();

$container['config'] = function () use ($settings){
    //Create the configuration
    return new App\Config($settings['data']);
};

$app->add($container->get('config'));

$app->add(new \App\IpFilterMiddleware($container['settings']['isp_allow']));

// Register Blade View helper
$container['view'] = function ($container) {
    return new \Slim\Views\Blade(
        $container['settings']['renderer']['blade_template_path'],
        $container['settings']['renderer']['blade_cache_path']
    );
};


$app->add(new \Slim\HttpCache\Cache('public', 86400));

//$conf = Noodlehaus\Config::load('../config.json');


$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "realm" => "Protected",
    "secure" => false,
    "users" => $container['settings']['users'],
    "error" => function ($request, $response, $arguments){
        return $response->write('username or password error');
    }
]));

$app->add(
    new \Bairwell\MiddlewareCors([
        'origin' => ['*.redis-web2.dev','*.dev','dev.*'],
        'allowCredentials' => true,
        'maxAge'           => 120,
        'allowHeaders'     => ['Accept', 'Accept-Language', 'Authorization', 'Content-Type','DNT','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Origin'],
        'allowMethods'     => 'GET,HEAD,PUT,POST,DELETE'
    ])
); // add CORs




//$container['cache'] = function () {
//    return new \Slim\HttpCache\CacheProvider();
//};

//$container['logger'] = function($c) {
//    $logger = new \Monolog\Logger('my_logger');
//    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
//    $logger->pushHandler($file_handler);
//    return $logger;
//};
//$app->add($container->get('logger'));