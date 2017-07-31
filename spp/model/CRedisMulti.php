<?php
namespace spp\model;

class CRedisMulti
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

    /**
     * 执行多条redis命令
     * @param $key string
     */
    public function multi($key=\Redis::PIPELINE)
    {
        $this->redis->multi($key);
    }

    /**
     * 执行多条redis命令
     * redis exec method.
     * @return bool|string
     */
    public function exec()
    {
        return $this->redis->exec();
    }
}

