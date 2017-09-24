<?php

namespace App\RedisWeb;

class Helpers extends Base
{

    public function generateRedisDataLite($data) {
        $result = []; // Array to hold our top namespaces.

        // Build an array of nested arrays containing all our namespaces and containing keys.
        foreach ($data as $key) {
            // Ignore keys that are to long (Redis supports keys that can be way to long to put in an url).
            if (strlen($key) > config('config.maxkeylen')) continue;

            //$type = $this->redis->type($key);
            $key = explode(config('config.seperator'), $key);

            // $d will be a reference to the current namespace.
            $d = &$result;

            // We loop though all the namespaces for this key creating the array for each.
            // Each time updating $d to be a reference to the last namespace so we can create the next one in it.
            for ($i = 0; $i < (count($key) - 1); $i++) {
                if (!isset($d[$key[$i]])) $d[$key[$i]] = [];
                $d = &$d[$key[$i]];
            }
            //dd($namespaces);

            // Nodes containing an item named __phpredisadmin__ are also a key, not just a directory.
            // This means that creating an actual key named __phpredisadmin__ will make this bug.
            $d[last($key)] = 'key';

            // Unset $d so we don't accidentally overwrite it somewhere else.
            unset($d);
        }
        return $result;
    }

    public function generateRedisData($data) {
        $result = []; // Array to hold our top namespaces.
        // Build an array of nested arrays containing all our namespaces and containing keys.

        foreach ($data as $inc => $key) {
            // Ignore keys that are to long (Redis supports keys that can be way to long to put in an url).
            if (strlen($key) > config('config.maxkeylen')) continue;

            //$type = $this->redis->type($key);
            $key = explode(config('config.seperator'), $key);

            // $d will be a reference to the current namespace.
            $d = &$result;
            $link = '';
            // We loop though all the namespaces for this key creating the array for each.
            // Each time updating $d to be a reference to the last namespace so we can create the next one in it.
            for ($i = 0; $i < count($key); $i++) {
                $link .= (($link === '') ? '' :  ':') . $key[$i];
                if (!isset($d[$key[$i]])) $d[$key[$i]] = ['name' => $key[$i], 'link' => $link];
                if ($i < count($key)-1) $d = &$d[$key[$i]]['children'];
            }

            // Unset $d so we don't accidentally overwrite it somewhere else.
            unset($d);
        }
//        $result['tree'] = $result;
        return ['name' => 'root', 'children' => $result];
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
                $count = config('config.count_per_page');
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
        if (config('charset', false)) {
            return mb_convert_encoding($str, config('charset'), 'utf-8');
        } else {
            return $str;
        }
    }

}