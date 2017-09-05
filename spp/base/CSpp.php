<?php
namespace spp\base;

use app2\controller\CIndexController;
use spp\component\CLog;
use spp\component\loghandler\CLogFileHandler;
use spp\component\CUtils;


class CSpp
{
    private static $instance = null;
    private $log = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self();
        return self::$instance;
    }

    public function setLogger($log)
    {
        $this->log = $log;
    }

    public function init()
    {

        CRuntime::init();

        if (isset(\Config::$log) && isset(\Config::$log['path']) && isset(\Config::$log['level'])) {
            $logHandler = new CLogFileHandler(\Config::$log['path']."/". date('Y-m-d') . '.log');
            $this->log = new CLog($logHandler, \Config::$log['level']);
        }
    }

    public function getLogger()
    {
        return $this->log;
    }

    public function run()
    {
        try {
            CUrlMgr::getInstance()->init();

            $moduleName = CUrlMgr::getInstance()->getModule();
            $conName = CUrlMgr::getInstance()->getController();
            $actName = CUrlMgr::getInstance()->getAction();
            $conName = $moduleName . "\\controller\\" . $conName;

            $controller = new $conName;

            if (!method_exists($controller, $actName)) {
                throw new CSPPException('can not find ' . $actName . '() in class ' . $conName, CError::ERR_NOT_FOUND_METHOD);
            }

            $next = $controller->before();

            if ($next) {
                $fireArgs = [];
                $method = new \ReflectionMethod($conName, $actName);
                foreach ($method->getParameters() as $arg) {
                    if ($_REQUEST[$arg->name]) {
                        $fireArgs[$arg->name] = $_REQUEST[$arg->name];
                    } else {
                        $fireArgs[$arg->name] = null;
                    }
                }
                $method->invokeArgs($controller, $fireArgs);
            }
            $controller->after();

        } catch (\Exception $e) {
            $this->processException($e);
        }

    }

    public function processException($e)
    {

        if (isset(\Config::$error) && isset(\Config::$error['handler'])) {

            $info = explode("::", \Config::$error['handler']);

            $conName = $info[0];
            $actName = $info[1];

            $controller = new $conName;
            $next = $controller->before();
            if ($next)
                $controller->$actName($e);
            $controller->after();
        } else {
            echo "<b>Exception:</b><br/>";
            echo "<b>message: </b>" . $e->getMessage() . ", <b>code: </b>" . $e->getCode() . "<br/>";
            echo "<b>file: </b>" . $e->getFile() . ", <b>line: </b>" . $e->getLine() . "<br/>";
            echo "<b>backtrace: </b>" . "<br>";
            echo str_replace("\n", "\n<br/>", $e->getTraceAsString());
        }

    }

}

class CController
{
    public function before()
    {
        return true;
    }

    public function after()
    {
    }

    protected $logger = null;

    public function __construct()
    {
        $this->logger = CSpp::getInstance()->getLogger();
    }

    static public function renderHtml($template, $data)
    {
        extract($data);

        try {
            if (isset(\Config::$tpl) && isset(\Config::$tpl['path'])) {
                include(\Config::$tpl['path'] . '/' . $template);
            } else {
                include($template);

            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    static public function renderJson($data)
    {
        @header('Content-type: application/json;charset=UTF-8');
        echo CUtils::json_encode($data);
    }


    public static function getQueryDe($key, $default)
    {
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }

    public static function getPostDe($key, $default)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        return $default;
    }

    public static function getCookieDe($key, $default)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        return $default;
    }

    public static function getRequestDe($key, $default)
    {
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }
        return $default;
    }

    public static function getQuery($key)
    {
        return $_GET[$key];
    }

    public static function getPost($key)
    {
        return $_POST[$key];
    }

    public static function getCookie($key)
    {
        return $_COOKIE[$key];
    }

    public static function getRequest($key)
    {
        $val = $_REQUEST[$key];
        return $val;
    }

    public static function setCookie($name, $value, $expire = 0, $path = "", $domain = "")
    {
        return setcookie($name, $value, $expire, $path, $domain);
    }

}


class CRuntime
{
    static function init()
    {
        if (isset(\Config::$error) && isset(\Config::$error['display'])) {
            ini_set('display_errors', \Config::$error['display']);
        }

        if (isset(\Config::$error) && isset(\Config::$error['level'])) {
            error_reporting(\Config::$error['level']);
        }

        ini_set('date.timezone', \Config::$timezone);

        set_error_handler('spp\base\CRuntime::errorHandler', \Config::$error['level']);

        $path[] = BASE_PATH;
        $path[] = BASE_PATH . "/modules";
		if(isset(\Config::$autoload) && is_array(\Config::$autoload)){
			$path = array_merge($path,\Config::$autoload);      
		}

        set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $path));

        spl_autoload_register(array('spp\base\CRuntime', 'loadClass'));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (error_reporting() == 0) return;

        throw new CSPPException($errstr, 0, $errno, $errfile, $errline);
    }


    public static function loadClass($class)
    {
        $path = str_replace('\\', '/', $class);
        $path .= '.php';

        include($path);
        if (!class_exists($class) && !interface_exists($class)) {
            throw new CSPPException("Can not find class " . $class . " in file " . $path, CError::ERR_NOT_FOUND_CLASS);
        }

    }
}

class CUrlMgr
{
    private $pathInfo = null;

    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self();
        return self::$instance;
    }

    public function init()
    {
        $this->pathInfo[0] = \Config::$modules[0];
        $this->pathInfo[1] = 'index';
        $this->pathInfo[2] = 'index';
        $this->procRequest();
    }

    private function procRequest()
    {

        $pathUri = '';
        $pos = strpos($_SERVER['REQUEST_URI'], $_SERVER["SCRIPT_NAME"]);

        if ($pos !== FALSE) {
            $pathUri = substr($_SERVER['REQUEST_URI'], strlen($_SERVER["SCRIPT_NAME"]));
        } else {
            $pathUri = substr($_SERVER['REQUEST_URI'], strlen(dirname($_SERVER["SCRIPT_NAME"])));
        }

        if (strlen($pathUri) > 0) {
            if ($pathUri[0] == '/')
                $pathUri = substr($pathUri, 1);

            $pathArray = explode('?', $pathUri);

            if ($pathArray[0] == '' && $_GET['r'] != '') {
                $pathArray[0] = $_GET['r'];
            }

            if (\Config::$urls['/' . $pathArray[0]] != null) {
                $pathArray[0] = \Config::$urls['/' . $pathArray[0]];
            }

            if ($pathArray[0] != '') {
                $this->pathInfo = explode('/', $pathArray[0]);
                if (!in_array($this->pathInfo[0], \Config::$modules)) {
                    $path[0] = \Config::$modules[0];
                    for ($i = 0; $i < count($this->pathInfo); $i++) {
                        $path[$i + 1] = $this->pathInfo[$i];
                    }
                    $this->pathInfo = $path;
                }
            }

        } else {
            if (\Config::$urls['/'] != null) {
                $pathArray[0] = \Config::$urls['/'];
                $this->pathInfo = explode('/', $pathArray[0]);

                if(!in_array($this->pathInfo[0],\Config::$modules)){
                    $path[0] = \Config::$modules[0];
                    for ($i = 0; $i < count($this->pathInfo); $i++) {
                        $path[$i + 1] = $this->pathInfo[$i];
                    }
                    $this->pathInfo = $path;
                }
            }
        }
    }

    private static function toCamel($str, $class = false)
    {
        $len = strlen($str);
        $out = '';
        for ($i = 0; $i < $len; ++$i) {
            $ch = $str[$i];
            if ($class && $i == 0) {
                $out .= strtoupper($ch);
            } else {
                if ($ch == '_') {
                    $i++;
                    $out .= strtoupper($str[$i]);
                } else {
                    $out .= $ch;
                }
            }
        }
        return $out;
    }

    public function getAction()
    {

        if ($this->pathInfo[2] == '') {
            return 'indexAction';
        } else {
            return $action = self::toCamel($this->pathInfo[2]) . 'Action';
        }
    }

    public function getController()
    {
        if ($this->pathInfo[1] == '') {
            return 'CIndexController';
        } else {
            return 'C' . self::toCamel($this->pathInfo[1], true) . 'Controller';
        }
    }

    public function getModule()
    {
        if ($this->pathInfo[0] == '') {
            return \Config::$modules[0];

        } else {
            return $this->pathInfo[0];
        }
    }
}

class CSPPException extends \ErrorException
{

}

class CError
{
    const ERR_NOT_FOUND_CLASS = 1001;
    const ERR_NOT_FOUND_METHOD = 1002;
    const ERR_URL_ROUTER = 1003;
}


