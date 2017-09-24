<?php

namespace App\RedisWeb;

use Illuminate\Http\Request;

class Base
{
    public function __construct(Request $r){
        $this->redis = \Redis::connection($r->get('server', 'default'));
        $this->redis->select($r->get('db', 0));
    }

}