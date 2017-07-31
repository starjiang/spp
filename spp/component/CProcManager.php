<?php
namespace spp\component;

class CProcManager
{
    private static $instance = null;
    private $procs = [];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    public function run($func, $procNum = 8)
    {
        for ($i = 0; $i < $procNum; ++$i) {
            $pid = pcntl_fork();
            if ($pid < 0) {
                echo "fork process fail\n";
            } else if ($pid == 0) {
                $func();
                exit();
            } else {
                echo "fork process " . $pid . "\n";
                $this->procs[] = $pid;
            }
        }

        foreach ($this->procs as $proc) {
            pcntl_waitpid($proc, $status);
        }
    }
}