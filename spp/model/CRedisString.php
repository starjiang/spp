<?php
namespace spp\model;

class CRedisString
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
        $this->redis = $redis;
    }


    public function delete()
    {
        $this->redis->delete($this->prefix);
    }

    /**
     * 带过期时间的Set
     * @param $value
     * @param $time
     * @return mixed
     */
    public function setex($value, $time)
    {
        $key = $this->prefix;

        return $this->redis->setex($key, $time, $value);
    }

    /**
     * Set
     * @param $value
     * @param $time
     * @return mixed
     */
    public function set($value)
    {
        $key = $this->prefix;

        return $this->redis->set($key, $value);
    }

    /**
     * get
     * @param $value
     * @param $time
     * @return mixed
     */
    public function get()
    {
        $key = $this->prefix;

        return $this->redis->get($key);
    }

    /**
     * incr
     * @return mixed
     */
    public function incr()
    {
        $key = $this->prefix;

        return $this->redis->incr($key);
    }

    /**
     * decr
     * @return mixed
     */
    public function decr()
    {
        $key = $this->prefix;

        return $this->redis->decr($key);
    }

    /**
     * incrby
     * @param $num int 要增加的数额
     * @return mixed
     */
    public function incrBy($num)
    {
        $key = $this->prefix;

        return $this->redis->incrBy($key, $num);
    }

    /**
     * exists
     * @return mixed
     */
    public function exists()
    {
        $key = $this->prefix;

        return $this->redis->exists($key);
    }

    /**
     * expire
     * @param $ttl int 生存时间
     * @return mixed
     */
    public function expire($ttl)
    {
        $key = $this->prefix;

        return $this->redis->expire($key, $ttl);
    }


}

