<?php

namespace App;

use App\Redis\Export;
use App\Redis\Helpers;

class DataController
{
    /** @var \Predis\Client */
    protected $redis;
    /**@var \Noodlehaus\Config */
    protected $config;
    protected $key;
    /** @var \App\Redis\Helpers */
    protected $helpers;
    /** @var \App\Redis\Export */
    protected $export;
    /** @var \Slim\Http\Request $request  PSR7 request */
    private $request;
    /** @var \Slim\Http\Response $response  PSR7 request */
    private $response;

    public function __call($name, array $arguments)
    {
        list($this->request, $this->response, $args) = $arguments;
        $server = $this->config->get('redis.'.$this->request->getParam('server', 'default'));
        $db = $this->request->getParam('db', 0);

        $this->redis = new \Predis\Client($server);
        $this->redis->select($db);
        $this->key = urldecode($this->request->getParam('key', '*'));

        $this->helpers = new Helpers($server, $this->config, $db);
        $this->export = new Export($server, $this->config, $db);

        return $this->$name();
    }

    /**
     * Config middleware invokable class.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $response = $next($request, $response);
        $response->getBody()->write('AFTER');
        return $response;
    }

    /**
     * @param $config \Noodlehaus\Config
     */
    public function __construct($config){
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    private function getList() {
        $next = $this->request->getParam('next', 0);
        $all = $this->request->getParam('all', $this->config->get('show_all_item', false));
        $count = $this->request->getParam('count', 10000);
        $filter =  $this->request->getParam('filter', $this->request->getParam('filter', $this->config->get(('filter'))));
        $redis_data = [];
        while (true) {
            list($next, $keys) = $this->redis->scan($next, [
                'MATCH' => $filter,
                'COUNT' => $count
            ]);
            $redis_data = array_merge($keys, $redis_data);
            if ($next == 0 || !$all) break;
        }

        $result = $this->helpers->generateRedisData($redis_data);
        sort($redis_data);

        $servers = $this->config->get('redis');
        foreach ($servers as &$server) $server = $server['host'];

        return $this->response->withJSON([
            'params' => [
                'next' => $next,
                'filter' => $filter,
                'count' => $count,
                'servers' => $servers,
                'show_all_item' => $all
            ],
            'tree'=> $result,
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    private function getQueues() {
        $loop = $this->redis->pubSubLoop();
        $loop->subscribe('rrrr');
        foreach ($loop as $val => $message) {
            /** @var \stdClass $message */
            if ($message->kind === 'message') {
                var_dump($message, $val);
                return $this->response->withJSON([
                    'line' => ''
                ], 200, JSON_UNESCAPED_UNICODE);
            }
        }

        return '111';
    }

    /**
     * @return mixed
     */
    private function test() {
        $this->redis->publish('rrrr', 'tttt');
        return $this->response->withJSON([
            'line' => ''
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    private function getItem() {
        try {
            $encoding = $this->redis->object('encoding', $this->key);
        } catch (\Exception $e) {
            $encoding = false;
        }
        /** @var $status \Predis\Response\Status */
        $status = $this->redis->type($this->key);
//        dd($r->key, $this->redis->type($r->key));
        $type = $status->getPayload();
        $page = $this->request->getParam('page_list', 1) - 1;

        list($values, $size) = $this->helpers->getParams($type, $this->key, $page);

        if (isset($values) && $this->config->get('count_elements_page', false)) {
            $count = $this->request->getParam('count_elements_page');
            $page  = $this->request->getParam('page', 1);
            $values = array_slice($values, $count * ($page - 1), $count,true);
        }

        if(is_null($values)) $values = [];

        return $this->response->withJSON([
            'count_elements_page' => $this->config->get('count_elements_page', false),
            'page' => $page,
            'type' => $type,
            'exists' => $this->redis->exists($this->key),
            'ttl' => $this->redis->ttl($this->key),
            'encoding' => $encoding,
            'values' => $values,
            'size' => $size,
            'key' => $this->key,
//            'count' => count($values),
            'max_count' => $this->config->get('count_per_page'),
            'pages' => ceil($size / $this->config->get('count_per_page'))
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    private function getInfo() {
        if ($this->request->getParam('reset', false) && method_exists($this->redis, 'resetStat')) {
            $this->redis->resetStat();
        }
        $info = $this->redis->info();

        return $this->response->withJSON([
            'info' => $info,
            'overview' => [
                'redis_version' => $info["Server"]['redis_version'],
                'uptime_in_seconds' => $info["Server"]['uptime_in_seconds'],
                'used_memory' => $info["Memory"]['used_memory'],
                'size' => $this->redis->dbSize(),
                'rdb_last_save_time' => $info['Persistence']['rdb_last_save_time']
            ],
            'server' => $this->config->get('redis.default.host')
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    public function setTTL() {
        if ($this->request->getAttribute('reset', -1 ) == -1) $this->redis->persist($this->key);
        else $this->redis->expire($this->key, $this->request->getAttribute('ttl'));

        return $this->response->withJSON([
            'result' => true
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    public function setName() {
        if (strlen($this->request->getAttribute('new_name')) > $this->config->get('maxkeylen')) {
            return $this->response->withJSON([
                'result' => 'ERROR: Your key is to long (max length is '.$this->config->get('maxkeyle').')'
            ], 400, JSON_UNESCAPED_UNICODE);
        }

        $this->redis->rename($this->request->getAttribute('old_name'), $this->request->getAttribute('new_name'));
        return $this->response->withJSON([
            'result' => $this->request->getAttribute('new_name')
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    public function removeItem() {
        if ($this->request->getAttribute('type', false) === 'string') {
            // Delete the whole key.
            $this->redis->del($this->key);
        } else if ($this->request->getAttribute('type', false) === 'tree') {
            $keys = $this->redis->keys($this->key.':*');
            foreach ($keys as $key) $this->redis->del($key);
        } else {
            $status = $this->redis->type($this->key);
            $type = $status->getPayload();
            if ($type === 'string') $this->redis->del($this->key);
            else if ($type === 'hash') $this->redis->hDel($this->key, $this->request->getAttribute('u_key'));
            else if ($type === 'list') {
                // Lists don't have simple delete operations.
                // You can only remove something based on a value so we set the value at the index to some random value we hope doesn't occur elsewhere in the list.
                $value = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(69/strlen($x)) )),1,69);

                // This code assumes $value is not present in the list. To make sure of this we would need to check the whole list and place a Watch on it to make sure the list isn't modified in between.
                $this->redis->lSet($this->key, $this->request->getAttribute('u_key'), $value);
                $this->redis->lRem($this->key, 1, $value);
            }
        }

        return $this->response->withJSON([
            'result' => true
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    public function export() {
        $keys = $this->redis->keys($this->key);

        $type = $this->request->getAttribute('type', 'redis');
        $vals = $type === 'json' ? [] : '';
        foreach ($keys as $key) {
            if($type === 'json') $vals[$key] = $this->export->export_json($key);
            else $vals .= $this->export->export_redis($key);
        }

        if($type === 'json') return $this->response->withJSON([$vals], 200, JSON_UNESCAPED_UNICODE);
        $this->response->withHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->response->withHeader('Content-Disposition', 'inline; filename="export.redis"');
        $this->response->write($vals);
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function saveItem() {
        if (strlen($this->request->getAttribute('new_key')) > $this->config->get('maxkeylen')) {
            return $this->response->withJSON([
                'result' => 'ERROR: Your hash key is to long (max length is '.$this->config->get('maxkeylen').')'
            ],200, JSON_UNESCAPED_UNICODE);
        }
        $new_key = $this->request->getAttribute('new_key', '');
        $new_value = $this->request->getAttribute('new_value', '');
        $old_value = $this->request->getAttribute('old_value', '');
        $u_key = $this->request->getAttribute('u_key', '');
        
        
        if ($this->request->getAttribute('type', 'string') === 'string') $this->redis->set($new_key, $new_value);
        else if ($this->request->getAttribute('type')  == 'hash') {
            if (!$this->redis->hExists($new_key, $u_key)) $this->redis->hDel($new_key, $u_key);
            $this->redis->hSet($new_key, $u_key, $new_value);
        } else if ($this->request->getAttribute('type') == 'list') {
            $size = $this->redis->lLen($new_key);
            if ($u_key == '' || $u_key == $size || $u_key == -1) {
                // Push it at the end
                $this->redis->rPush($new_key, $new_value);
            } else if ($u_key >= 0 && $u_key < $size) {
                // Overwrite an index
                $this->redis->lSet($new_key, $u_key, $new_value);
            } else {
                die('ERROR: Out of bounds index');
            }
        } else if ($this->request->getAttribute('type') == 'set') {
            if ($new_value != $old_value) {
                // The only way to edit a Set value is to add it and remove the old value.
                if ($old_value !== '') $this->redis->sRem($new_key, $old_value);
                $this->redis->sAdd($new_key, $new_value);
            }
        } else if ($this->request->getAttribute('type') == 'zset') {
            // The only way to edit a ZSet value is to add it and remove the old value.
            if ($old_value !== '')  $this->redis->zRem($new_key, $old_value);
            $this->redis->zAdd($new_key, [$u_key => $new_value]);
        }

        return $this->response->withJSON([
            'result' => true
        ], 200, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed
     */
    public function import() {
        // Append some spaces at the end to make sure we always have enough arguments for the last function.
        $commands = str_getcsv(str_replace(["\r", "\n"], ['', ' '], $this->request->getAttribute('import_text') ).'    ', ' ');

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
        return $this->response->withJSON([
            'result' => true
        ], 200, JSON_UNESCAPED_UNICODE);
    }
}
