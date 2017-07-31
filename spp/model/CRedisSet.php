<?php

namespace spp\model;

class CRedisSet {

    static private $instances = [];
    private $prefix = '';
    private $redis = null;

    protected function __construct($prefix)
    {
        $this->prefix = $prefix;
        if (isset(\Config::$redis))
        {
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
        if (self::$instances[$key] == null)
        {
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
//        $key = $this->prefix . '.' . $id;
        return $this->redis->delete($this->prefix);
    }

    public function sadd($value)
    {
        $key = $this->prefix;
        return $this->redis->sadd($key, $value);
    }

    /**
     * 集合添加多个值
     * @param $value array
     * @return mixed
     */
    public function sAddArray(array $value)
    {
        $key = $this->prefix;
        return $this->redis->sAddArray($key, $value);
    }

    public function sismember($value)
    {
        $key = $this->prefix;
        return $this->redis->sismember($key, $value);
    }

    public function srem($value)
    {
        $key = $this->prefix;
        return $this->redis->srem($key, $value);
    }

    /**
     * 查询集合内所有元素
     * @return mixed
     */
    public function smembers()
    {
        $key = $this->prefix;
        return $this->redis->smembers($key);
    }

    /**
     * 统计set的元素的个数
     * @return int
     */
    public function scard()
    {
        $key = $this->prefix;
        return $this->redis->scard($key);
    }

    /**
     * 统计set的元素的个数
     * @return int
     */
    public function scardByKey($key)
    {
        $key = $this->prefix . $key;
        return $this->redis->scard($key);
    }

    /**
     * 返回一个随机元素
     * @return mixed
     */
    public function sRandmember()
    {
        $key = $this->prefix;
        return $this->redis->sRandMember($key);
    }

    /**
     * 检查key是否存在
     * @return type
     */
    public function exists()
    {
        $key = $this->prefix;
        return $this->redis->exists($key);
    }

    public function sscan($count, $cursor = null, $pattern = '')
    {
        $key = $this->prefix;
        $arr = [];
        $arr['data'] = $this->redis->sScan($key, $cursor, $pattern, $count);
        $arr['cursor'] = $cursor;
        return $arr;
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
