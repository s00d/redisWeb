<?php

namespace App\Redis;

class Helpers extends Base
{

    public function generateRedisData($data, $link, $paginate) {
        $result = []; // Array to hold our top namespaces.
        // Build an array of nested arrays containing all our namespaces and containing keys.

        if(!$paginate) $data = preg_replace("/^{$link}/i", "", $data);

        foreach ($data as $inc => $key) {
            // Ignore keys that are to long (Redis supports keys that can be way to long to put in an url).
            if (strlen($key) > $this->config->get('maxkeylen')) continue;

            //$type = $this->redis->type($key);
            if(!$paginate) {
                $key = explode($this->config->get('seperator'), $key);
                if (isset($result[$key[0]])) continue;
                $item =  ['name' => $key[0], 'link' => $link.$key[0]];
                $item['children'] = (count($key) > 1) ? 'unload': false;
                $result[$key[0]] = $item;
                continue;
            }


//            // $d will be a reference to the current namespace.
            $d = &$result;
            $link = '';
            // We loop though all the namespaces for this key creating the array for each.
            // Each time updating $d to be a reference to the last namespace so we can create the next one in it.
            for ($i = 0; $i < count($key); $i++) {
                if (isset($d[$key[$i]])) continue;
                $link .= (($link === '') ? '' :  ':') . $key[$i];
                if (!isset($d[$key[$i]])) $d[$key[$i]] = ['name' => $key[$i], 'link' => $link];
                if ($i < count($key)-1) $d = &$d[$key[$i]]['children'];
                break;
            }
//
//            // Unset $d so we don't accidentally overwrite it somewhere else.
            unset($d);
        }
//        $result['tree'] = $result;
        if($link && substr($link, -1) === ':') $link = substr($link, 0, -1);
        return ['name' => $link ? $link :'root', 'children' => $result];
    }

//    public function encodeOrDecode($action, $key, $data) {
//        if (!\Request::get('raw', false) || !empty(config('config.serialization'))) return $data;
//        foreach (config('config.serialization') as $pattern => $closures) {
//            if (fnmatch($pattern, $key)) {
//                return $closures[$action]($data);
//            }
//        }
//
//        return $data;
//    }

    public function getParams($type, $key, $page) {
        $data = [];
        switch ($type) {
            case 'string':
                $value = $this->redis->get($key);
                $size  = strlen($value);
                return [[$value], $size];
            case 'hash':
                $values = $this->redis->hGetAll($key);
                foreach ($values as $k => $value) {
                    $values[$k] = $value;
                }
                $size = count($values);
                ksort($values);
                return [$values, $size];

            case 'list':
                $size = $this->redis->lLen($_GET['key']);
                $count = $this->config->get('count_per_page_list');
                $values = $this->redis->lrange($key, $page * $count, $page * $count + $count);
                foreach ($values as $k => $value) {
                    $values[$k] = $value;
                }
                ksort($values);
                return [$values, $size];

            case 'set':
                $values = $this->redis->sMembers($key);
                foreach ($values as $k => $value) {
                    $values[$k] = $value;
                }
                $size = count($values);
                sort($values);
                return [$values, $size];

            case 'zset':
                $values = $this->redis->zRange($key, 0, -1);
                foreach ($values as $k => $value) {
                    $score         = $this->redis->zScore($key, $value);
                    $data[$score] = $value;
//                    $values[$k] = $this->encodeOrDecode('load', $key, $value);
                }
                $size = count($data);
                return [$data, $size];
        }
    }

    function input_convert($str) {
        if ($this->config->get('charset', false)) {
            return mb_convert_encoding($str, $this->config->get('charset'), 'utf-8');
        } else {
            return $str;
        }
    }

}