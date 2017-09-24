<?php

namespace App\Console\Commands;

use App\Events\SendRedisDataEvent;
use Illuminate\Console\Command;

class test extends Command
{

    public $update;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
//        \Broadcast::channel('queues', function () {
//            return 'darq';
//        });

        \Redis::publish('test-channel', json_encode(['foo' => 'bar']));
    }
}
