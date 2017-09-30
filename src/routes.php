<?php

use Slim\Http\Request;
use Slim\Http\Response;
use App\DataController;


$app->get('/', function (Request $request, Response $response, array $args) {
    $config = $this->config;
    $this->logger->info("Slim-Skeleton '/' route");

    return $this->view->render($response, 'index', [
        'name' => $config->get('name'),
    ]);
});

$app->group('/api', function () {
    $controller = new DataController($this->getContainer()->get('config'));
    $this->get('/getList', [$controller, 'getList']);
    $this->get('/getItem', [$controller, 'getItem']);
    $this->get('/getInfo', [$controller, 'getInfo']);

    $this->post('/setTTL', [$controller, 'setTTL']);
    $this->post('/setName', [$controller, 'setName']);
    $this->post('/saveItem', [$controller, 'saveItem']);
    $this->delete('/removeItem', [$controller, 'import']);

    $this->get('/export', [$controller, 'export']);
    $this->post('/import', [$controller, 'import']);

    $this->get('/getQueues', [$controller, 'getQueues']);
    $this->get('/test', [$controller, 'test']);
});