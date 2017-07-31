<?php
namespace spp\model;

class CRedisMapper
{
    static private $instances = [];
    private $prefix = '';
    private $redis = null;

    protected function __construct($prefix='')
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


    public static function getInstance($prefix='')
    {
        $key = $prefix;
        if (self::$instances[$key] == null) {
            self::$instances[$key] = new self($prefix);
        }
        return self::$instances[$key];
    }

    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    public function set($id, $obj)
    {
        $key = $this->prefix . '.' . $id;
        $value = \spp\component\CUtils::json_encode($obj);
        return $this->redis->set($key, $value);
    }

    public function get($id)
    {
        $key = $this->prefix . '.' . $id;
        $value = $this->redis->get($key);
        $value = json_decode($value);
        return $value;
    }

    public function mget($ids)
    {
        $keys = [];
        foreach ($ids as $id) {
            $keys[$this->prefix . '.' . $id] = $id;
        }
        $values = $this->redis->getMultiple(array_keys($keys));
        $objs = [];
        foreach ($values as $key => $value) {
            if ($value != false) {
                $objs[$ids[$key]] = json_decode($value);
            }
        }
        return $objs;
    }

    public function delete($id)
    {
        $key = $this->prefix . '.' . $id;
        return $this->redis->delete($key);
    }

}

