<?php
namespace spp\model;

class CRedisList
{
    static private $instances = [];
    private $prefix = '';
    private $redis = null;

    protected function __construct($prefix)
    {
        $this->prefix = $prefix;
        if (isset(\Config::$redis)) {
            $this->redis = CConnMgr::getInstance()->redis(\Config::$redis);
        }
    }

    public static function newInstance()
    {
        $caller = get_called_class();
        return new $caller();
    }


    public static function getInstance($prefix)
    {
        $key = $prefix;
        if (self::$instances[$key] == null) {
            self::$instances[$key] = new self($prefix);
        }
        return self::$instances[$key];
    }

    public function setRedis($redis)
    {
        return $this->redis = $redis;
    }

    public function lpush($item)
    {
        return $this->redis->lPush($this->prefix, $item);
    }

    public function lpop()
    {
        return $this->redis->lPop($this->prefix);
    }

    function rpush($item)
    {
        $this->redis->rPush($this->prefix, $item);
    }

    public function rpop()
    {
        return $this->redis->rPop($this->prefix);
    }

    public function brpop()
    {
        return $this->redis->brPop($this->prefix);
    }

    public function blpop()
    {
        return $this->redis->blPop($this->prefix);
    }

    public function get($index)
    {
        return $this->redis->lIndex($this->prefix, $index);
    }

    public function set($index, $item)
    {
        return $this->redis->lSet($this->prefix, $index, $item);
    }

    public function getRange($start, $end)
    {
        return $this->redis->lRange($this->prefix, $start, $end);
    }

    public function remove($start, $end)
    {
        return $this->redis->lTrim($this->prefix, $start, $end);
    }

    public function size()
    {
        return $this->redis->lSize($this->prefix);
    }

    /**
     * 移除list的指定值
     * @param mixed $value
     * @param int $count
     * @return mixed
     */
    public function lRem($value, $count = 0)
    {
        return $this->redis->lRem($this->prefix, $value, $count);
    }

    public function expire($ttl)
    {
        return $this->redis->expire($this->prefix, $ttl);
    }

    public function delete()
    {
        return $this->redis->delete($this->prefix);
    }
}

