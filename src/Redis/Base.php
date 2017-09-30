<?php

namespace App\Redis;

use Slim\Http\Request;

class Base
{
    /** @var \Predis\Client */
    protected $redis;
    /** @var \Noodlehaus\Config */
    protected $config;

    public function __construct($server, $config, $db){
        $this->redis = new \Predis\Client($server);
        $this->redis->select($db);
        $this->config = $config;
    }

}