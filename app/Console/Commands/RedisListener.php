<?php

namespace App\Console\Commands;

use App\Events\SendRedisDataEvent;
use Illuminate\Console\Command;

class RedisListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redis = \Redis::connection('publisher');
        $redis->psubscribe(['*'], function($message, $channel) {
            if($channel === 'queues') return;
            var_dump($message, $channel);
            event(new SendRedisDataEvent($channel, $message));
        });
    }
}
