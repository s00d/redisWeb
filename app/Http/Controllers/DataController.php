<?php

namespace App\Http\Controllers;

use App\RedisWeb\Export;
use App\RedisWeb\Helpers;
use Illuminate\Http\Request;

class DataController extends Controller
{
    private $redis;

    public function __construct(Request $r){
        $this->redis = \Redis::connection($r->get('server', 'default'));
        $this->redis->select($r->get('db', 0));
        if($r->has('key')) $r->key = urldecode($r->key);
    }

    public function getList(Request $r, Helpers $helpers) {
        $next = $r->get('next', 0);
        $all = $r->get('all', config('config.show_all_item', false));
        $count = $r->get('count', 10000);
        $filter =  $r->get('filter', $r->get('filter', config('config.filter')));
        $redis_data = [];
        while (true) {
            list($next, $keys) = $this->redis->scan($next, [
                'MATCH' => $filter,
                'COUNT' => $count
            ]);
            $redis_data = array_merge($keys, $redis_data);
            if ($next == 0 || !$all) break;

        }
        if ($r->get('lite', false)) $result = $helpers->generateRedisDataLite($redis_data);
        else $result = $helpers->generateRedisData($redis_data);
        sort($redis_data);

        $servers = config('database.redis');
        unset($servers['client']);
        foreach ($servers as &$server) $server = $server['host'];


        return response()->json([
            'params' => [
                'next' => $next,
                'filter' => $filter,
                'count' => $count,
                'servers' => $servers,
                'show_all_item' => $all
            ],
            'tree'=> $result,
        ]);
    }

    public function getItem(Request $r, Helpers $helpers) {
        try {
            $encoding = $this->redis->object('encoding', $r->key);
        } catch (\Exception $e) {
            $encoding = false;
        }
        /** @var $status \Predis\Response\Status */
        $status = $this->redis->type($r->key);
//        dd($r->key, $this->redis->type($r->key));
        $type = $status->getPayload();
        $page = $r->get('page_list', 1) - 1;

        list($values, $size) = $helpers->getParams($type, $r->key, $page);

        if (isset($values) && config('count.count_elements_page', false)) {
            $count = $r->get('count_elements_page');
            $page  = $r->get('page', 1);
            $values = array_slice($values, $count * ($page - 1), $count,true);
        }

        if(is_null($values)) $values = [];

        return response()->json([
            'count_elements_page' => config('config.count_elements_page', false),
            'page' => $page,
            'type' => $type,
            'exists' => $this->redis->exists($r->key),
            'ttl' => $this->redis->ttl($r->key),
            'encoding' => $encoding,
            'values' => $values,
            'size' => $size,
            'key' => $r->key,
//            'count' => count($values),
            'max_count' => config('config.count_per_page'),
            'pages' => ceil($size / config('config.count_per_page'))
        ]);
    }

    public function getInfo(Request $r) {
        if ($r->has('reset') && method_exists($this->redis, 'resetStat')) {
            $this->redis->resetStat();
        }

        $info = $this->redis->info();
        return response()->json([
            'info' => $info,
            'overview' => [
                'redis_version' => $info["Server"]['redis_version'],
                'uptime_in_seconds' => $info["Server"]['uptime_in_seconds'],
                'used_memory' => $info["Memory"]['used_memory'],
                'size' => $this->redis->dbSize(),
                'rdb_last_save_time' => $info['Persistence']['rdb_last_save_time']
            ],
            'server' => config('database.redis.default.host')
        ]);
    }

    public function setTTL(Request $r) {
        if ($r->ttl == -1) $this->redis->persist($r->key);
        else $this->redis->expire($r->key, $r->ttl);
        return response()->json(['result' => true]);
    }

    public function setName(Request $r) {
        if (strlen($r->new_name) > config('config.maxkeylen')) {
            return response()->json([
                'result' => 'ERROR: Your key is to long (max length is '.config('config.maxkeyle').')'
            ], 400);
        }

        $this->redis->rename($r->old_name, $r->new_name);
        return response()->json(['result' => $r->new_name]);
    }

    public function removeItem(Request $r) {
        if ($r->get('type', false) === 'string') {
            // Delete the whole key.
            $this->redis->del($r->key);
        } else if ($r->get('type', false) === 'tree') {
            $keys = $this->redis->keys($r->key.':*');
            foreach ($keys as $key) $this->redis->del($key);
        } else {
            $status = $this->redis->type($r->key);
            $type = $status->getPayload();
            if ($type === 'string') $this->redis->del($r->key);
            else if ($type === 'hash') $this->redis->hDel($r->key, $r->u_key);
            else if ($type === 'list') {
                // Lists don't have simple delete operations.
                // You can only remove something based on a value so we set the value at the index to some random value we hope doesn't occur elsewhere in the list.
                $value = str_random(69);

                // This code assumes $value is not present in the list. To make sure of this we would need to check the whole list and place a Watch on it to make sure the list isn't modified in between.
                $this->redis->lSet($r->key, $r->u_key, $value);
                $this->redis->lRem($r->key, 1, $value);
            }
        }

        return response()->json(['result' => true]);
    }

    public function export(Request $r, Export $export) {
        $keys = $this->redis->keys($r->get('key', '*'));

        $type = $r->get('type', 'redis');
        $vals = $type === 'json' ? [] : '';
        foreach ($keys as $key) {
            if($type === 'json') $vals[$key] = $export->export_json($key);
            else $vals .= $export->export_redis($key);
        }

        if($type === 'json') return response()->json($vals);
        return response($vals)
            ->header('Content-type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="export.redis"');
    }

    public function saveItem(Request $r) {
        if (strlen($r->new_key) > config('config.maxkeylen')) {
            return response()->json(['result' => 'ERROR: Your hash key is to long (max length is '.config('config.maxkeylen').')']);
        }
        if ($r->type == 'string') $this->redis->set($r->new_key, $r->new_value);
        else if ($r->type == 'hash') {
            if (!$this->redis->hExists($r->new_key, $r->u_key)) $this->redis->hDel($r->new_key, $r->u_key);
            $this->redis->hSet($r->new_key, $r->u_key, $r->new_value);
        } else if ($r->type == 'list') {
            $size = $this->redis->lLen($r->new_key);
            if ($r->u_key == '' || $r->u_key == $size || $r->u_key == -1) {
                // Push it at the end
                $this->redis->rPush($r->new_key, $r->new_value);
            } else if ($r->u_key >= 0 && $r->u_key < $size) {
                // Overwrite an index
                $this->redis->lSet($r->new_key, $r->u_key, $r->new_value);
            } else {
                die('ERROR: Out of bounds index');
            }
        } else if ($r->type == 'set') {
            if ($r->new_value != $r->old_value) {
                // The only way to edit a Set value is to add it and remove the old value.
                if ($r->old_value !== '') $this->redis->sRem($r->new_key, $r->old_value);
                $this->redis->sAdd($r->new_key, $r->new_value);
            }
        } else if ($r->type == 'zset') {
            // The only way to edit a ZSet value is to add it and remove the old value.
            if ($r->old_value !== '')  $this->redis->zRem($r->new_key, $r->old_value);
            $this->redis->zAdd($r->new_key, [$r->u_key => $r->new_value]);
        }

        return response()->json(['result' => true]);
    }

    public function import(Request $r) {
        // Append some spaces at the end to make sure we always have enough arguments for the last function.
        $commands = str_getcsv(str_replace(["\r", "\n"], ['', ' '], $r->import_text).'    ', ' ');

        foreach ($commands as &$command) $command = stripslashes($command);
        unset($command);

        for($i = 0; $i < count($commands); ++$i) {
            if (empty($commands[$i])) continue;
            $commands[$i] = strtoupper($commands[$i]);

            switch ($commands[$i]) {
                case 'SET': {
                    $this->redis->set($commands[$i + 1], $commands[$i + 2]);
                    $i += 2;
                    break;
                }
                case 'HSET': {
                    $this->redis->hSet($commands[$i + 1], $commands[$i + 2], $commands[$i + 3]);
                    $i += 3;
                    break;
                }
                case 'LPUSH': {
                    $this->redis->lPush($commands[$i + 1], $commands[$i + 2]);
                    $i += 2;
                    break;
                }
                case 'RPUSH': {
                    $this->redis->rPush($commands[$i + 1], $commands[$i + 2]);
                    $i += 2;
                    break;
                }
                case 'LSET': {
                    $this->redis->lSet($commands[$i + 1], $commands[$i + 2], $commands[$i + 3]);
                    $i += 3;
                    break;
                }
                case 'SADD': {
                    $this->redis->sAdd($commands[$i + 1], $commands[$i + 2]);
                    $i += 2;
                    break;
                }
                case 'ZADD': {
                    $this->redis->zAdd($commands[$i + 1], $commands[$i + 2], $commands[$i + 3]);
                    $i += 3;
                    break;
                }
            }
        }
    }
}
