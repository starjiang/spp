<?php
namespace spp\model;

/**
 * Class CRedisZSet
 * @package spp\model
 */
class CRedisZSet
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

    public function add($score, $member)
    {
        $this->redis->zAdd($this->prefix, $score, $member);
    }

    public function remove($member)
    {
        $this->redis->zDelete($this->prefix, $member);
    }
    
    public function getByOffset($start, $end, $order = 'desc')
    {
        if ($order === 'desc') {
            return $this->redis->zRevRange($this->prefix, $start, $end);
        }

        return $this->redis->zRange($this->prefix, $start, $end);
    }

    public function getByScore($start, $end, $order = 'desc')
    {
        if ($order == 'desc') {
            return $this->redis->zRevRangeByScore($this->prefix, $start, $end);
        } else {
            return $this->redis->zRangeByScore($this->prefix, $start, $end);
        }
    }

    public function index($member)
    {
        return $this->redis->zRank($this->prefix, $member);
    }

    public function isMember($member)
    {
        return $this->redis->zScore($this->prefix, $member) !== false;

    }

    public function score($member)
    {
        return $this->redis->zScore($this->prefix, $member);
    }

    public function count($start, $end)
    {
        return $this->redis->zCount($this->prefix, $start, $end);
    }

    public function size()
    {
        return $this->redis->zSize($this->prefix);
    }
    /**
     * @author  sunny
     * @return type
     */
    public function zcard()
    {
        return $this->redis->zcard($this->prefix);
    }

    /**
     * zincrBy
     * @return mixed
     */
    public function zincrBy($member, $score = 1)
    {
        return $this->redis->zincrBy($this->prefix, $score, $member);
    }

    public function zRange($star, $end, $withscores = null)
    {
        return $this->redis->zRange($this->prefix, $star, $end, $withscores);
    }

    public function zRevRange($star, $end, $withscores = null)
    {
        return $this->redis->zRevRange($this->prefix, $star, $end, $withscores);
    }
    /**
     * 删除key
     * @return type
     * @author sunny
     */
    public function deleteKey()
    {
        return $this->redis->delete($this->prefix);
    }
    /**
     * 升序排名
     * @param type $member
     * @return type
     * @author 孙昌致<331942828@qq.com>
     */
    public function zRevRank($member)
    {
        return $this->redis->zRevRank($this->prefix,$member);
    }
    /**
     * 降序排名
     * @param type $member
     * @return type
     * @author 孙昌致<331942828@qq.com>
     */
    public function zRank($member)
    {
        return $this->redis->zRank($this->prefix,$member);
    }
}

