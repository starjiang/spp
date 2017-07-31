<?php
namespace spp\model;

class CConnMgr
{
    private $pdos = array();
    private $mongos = array();
    private $mems = array();
    private $rediss = array();

    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (CConnMgr::$instance == null) {
            CConnMgr::$instance = new CConnMgr();
        }
        return CConnMgr::$instance;
    }

    public function getPdo($dsn, $user, $pwd, $options)
    {

        $key = $dsn;

        if ($this->pdos[$key] == null) {
            $this->pdos[$key] = new \PDO($dsn, $user, $pwd, $options);
        }

        return $this->pdos[$key];

    }

    public function pdo(Array $info)
    {
        if ($info == false) {
            return null;
        }
        return $this->getPdo($info['dsn'], $info['user'], $info['pwd'], $info['options']);
    }

    public function getMongo($dsn, $options, $db)
    {
        $key = $dsn . "/" . $db;
        if ($this->mongos[$key] == null) {
            $mongoClient = new \MongoClient($dsn, $options);
            $this->mongos[$key] = $mongoClient->selectDb($db);;
        }
        return $this->mongos[$key];
    }

    public function mongo(Array $info)
    {

        if ($info == false) {
            return null;
        }

        return $this->getMongo($info['dsn'], $info['options'], $info['db']);
    }

    static function getMemKey($infos)
    {
        $key = '';
        foreach ($infos as $info) {
            $key .= $info['host'] . $info['port'];
        }

        return $key;
    }

    public function getMem($infos)
    {
        $key = static::getMemKey($infos);
        if ($this->mems[$key] == null) {
            $memcache = new \Memcache();

            foreach ($infos as $info) {
                $info['persist'] = (isset($info['persist']) ? $info['persist'] : true);
                $info['weight'] = (isset($info['weight']) ? $info['weight'] : 100);
                $info['timeout'] = (isset($info['timeout']) ? $info['timeout'] : 1);

                $memcache->addServer($info['host'], $info['port'], $info['persist'], $info['weight'], $info['timeout']);
            }
            $this->mems[$key] = $memcache;
        }

        return $this->mems[$key];
    }

    public function mem(Array $infos)
    {
        if ($infos == false) {
            return null;
        }
        return $this->getMem($infos);
    }

    public function getRedis($host, $port, $persist, $timeout)
    {
        $key = $host . $port;

        if ($this->rediss[$key] == null) {
            $redis = new \Redis();
            if ($persist)
                $redis->pconnect($host, $port, $timeout);
            else
                $redis->connect($host, $port, $timeout);

            $this->rediss[$key] = $redis;
        }

        return $this->rediss[$key];
    }

    public function redis(Array $info)
    {
        if ($info == false) {
            return null;
        }

        return $this->getRedis($info['host'], $info['port'], $info['persist'], $info['timeout']);

    }
}
