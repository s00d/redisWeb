<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\DataController;


$homePage = function(Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/queues' route");
    $redis = new \Predis\Client($this->config->get('redis.default'));

    return $this->view->render($response, 'index', [
        'name' => $this->config->get('name'),
        'redis_key' => $redis->get('redis-key')
    ]);
};

$app->get('/', $homePage);
$app->get('/queues', $homePage);

$app->group('/api', function () {
    $controller = new DataController($this->getContainer()->get('config'));
    $this->get('/getList', [$controller, 'getList']);
    $this->get('/getItem', [$controller, 'getItem']);
    $this->get('/getInfo', [$controller, 'getInfo']);

    $this->post('/setTTL', [$controller, 'setTTL']);
    $this->post('/runAction', [$controller, 'runAction']);

    $this->post('/setName', [$controller, 'setName']);
    $this->post('/saveItem', [$controller, 'saveItem']);
    $this->delete('/removeItem', [$controller, 'removeItem']);

    $this->get('/export', [$controller, 'export']);
    $this->post('/import', [$controller, 'import']);

    $this->get('/getQueues', [$controller, 'getQueues']);
    $this->get('/test', [$controller, 'test']);
});