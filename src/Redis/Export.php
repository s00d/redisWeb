<?php

namespace App\Redis;

class Export extends Base
{

    function export_json($key) {
        $type = $this->redis->type($key);
        if ($type == 'string') return $this->redis->get($key);
        else if ($type == 'hash') return $this->redis->hGetAll($key);
        else if ($type == 'set') return $this->redis->sMembers($key);
        else if ($type == 'zset') return $this->redis->zRange($key, 0, -1);
        else if ($type == 'list') {
            $size  = $this->redis->lLen($key);
            $value = [];
            for ($i = 0; $i < $size; ++$i) $value[] = $this->redis->lIndex($key, $i);
            return $value;
        }

        return null;
    }

    function export_redis($key) {
        $type = $this->redis->type($key);

        if ($type == 'string') {
            return 'SET "'.addslashes($key).'" "'.addslashes($this->redis->get($key)).'"'."\n";
        } else if ($type == 'hash') {
            $values = $this->redis->hGetAll($key);
            $text = '';
            foreach ($values as $k => $v) {
                $text .= 'HSET "'.addslashes($key).'" "'.addslashes($k).'" "'.addslashes($v).'"'. "\n";
            }
            return $text;
        } else if ($type == 'list') {
            $size = $this->redis->lLen($key);
            $text = '';
            for ($i = 0; $i < $size; ++$i) {
                $text .= 'RPUSH "'.addslashes($key).'" "'.addslashes($this->redis->lIndex($key, $i)).'"'. "\n";
            }
            return $text;
        } else if ($type == 'set') {
            $values = $this->redis->sMembers($key);
            $text = '';
            foreach ($values as $v) {
                $text .= 'SADD "'.addslashes($key).'" "'.addslashes($v).'"'. "\n";
            }
            return $text;
        } else if ($type == 'zset') {
            $values = $this->redis->zRange($key, 0, -1);
            $text = '';
            foreach ($values as $v) {
                $s = $this->redis->zScore($key, $v);

                $text .= 'ZADD "'.addslashes($key).'" '.$s.' "'.addslashes($v).'"'. "\n";
            }
            return $text;
        }
        return '';
    }

}