<?php

class CSpp
{
	private static $instance=null;
	private $log = null;
	
	private function __construct(){}
	

	
	public static function getInstance()
	{
		if(self::$instance == null)
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

		if(isset(CConfig::$log) && isset(CConfig::$log['path']) && isset(CConfig::$log['level']))
		{
			$logHandler= new CLogFileHandler(CConfig::$log['path'].date('Y-m-d').'.log');
			$this->log = new CLog($logHandler,CConfig::$log['level']);
		}

	}
	
	public function getLogger()
	{
		return $this->log;
	}
	
	public function run()
	{
		try
		{
			CUrlMgr::getInstance()->init();
			
			$conName=CUrlMgr::getInstance()->getController();
			$actName=CUrlMgr::getInstance()->getAction();
			$controller = new $conName;  
			
			if(!method_exists ($controller, $actName))
			{
				throw new CSPPException('can not find '.$actName.'() in class '.$conName,CError::ERR_NOT_FOUND_METHOD);
			}

			$next = $controller->before();
			
			if($next)
			{
				$method = new ReflectionMethod($conName, $actName);
				$method->invokeArgs($controller, CUrlMgr::getInstance()->getParams());
				//$controller->$actName();
			}
			
			$controller->after();
		}
		catch(Exception $e)
		{
			$this->processException($e);
		}
		
	}
	
	public function processException($e)
	{
		
		if(isset(CConfig::$error) && isset(CConfig::$error['cls']) && isset(CConfig::$error['method']))
		{
			
			$conName = CConfig::$error['cls'];
			$actName = CConfig::$error['method'];
			
			$controller=new $conName;
			
			$next = $controller->before();
			if($next)
				$controller->$actName($e);
			$controller->after();
		}
		else 
		{
			echo "<b>Exception:</b><br/>";
			echo "<b>message: </b>".$e->getMessage().", <b>code: </b>".$e->getCode()."<br/>";
			echo "<b>file: </b>".$e->getFile().", <b>line: </b>".$e->getLine()."<br/>";
			echo "<b>backtrace: </b>"."<br>";
			echo str_replace("\n","\n<br/>", $e->getTraceAsString());
		}
		
	}

}

class CController
{
	public $data = array();
	public function before(){ return true; }
	public function after(){}
	
	public function render($template,$flush=true)
	{
		ob_start();
		
		extract($this->data);
		
		try
		{
			if(isset(CConfig::$tpl) && isset(CConfig::$tpl['path']))
			{
				include(CConfig::$tpl['path'].'/'.$template);
			}
			else
			{
				include($template);
			}
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}

		
		$content=ob_get_contents();
		
		if($flush)
		{
			ob_end_flush();		
		}
		else
		{
			ob_end_clean();
		}
		return $content;
	}
}


class CRuntime
{
	static function init()
	{
		if(isset(CConfig::$error) && isset(CConfig::$error['display']))
		{
			ini_set('display_errors',CConfig::$error['display']);
		}
		
		if(isset(CConfig::$error) && isset(CConfig::$error['level']))
		{
			error_reporting(CConfig::$error['level']);
		}
		
		ini_set('date.timezone','Asia/Shanghai');
		
		$path[] = SPP_PATH."/model";
		$path[] = SPP_PATH."/model/cache";
		$path[] = SPP_PATH."/component";
		$path[] = SPP_PATH."/component/loghandler";
		$path[] = SPP_PATH."/base";

		if(isset(CConfig::$path) && is_array(CConfig::$path))
		{
			$path = array_merge($path,CConfig::$path);
		}

		
		set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR,$path));
		
		spl_autoload_register(array('CRuntime', 'loadClass'));
	}
	
	public static function loadClass($class)
	{
	
		$fileName = $class.'.php';
		include ($fileName);
		if(!class_exists($class) && !interface_exists($class))
		{
			throw new CSPPException("Can not find class ".$class." in file ".$fileName,CError::ERR_NOT_FOUND_CLASS);
		}
	}
	

}


class CUrlMgr
{
	private $pathInfo = null;

	private static $instance = null;
	
	private function __construct(){}
	
	
	public static function getInstance()
	{
		if(self::$instance == null)
			self::$instance = new self();
		return self::$instance;
	}
	

	public function init()
	{
		$this->pathInfo[0]='index';
		$this->pathInfo[1]='index';
		
		$this->procRequest();
	}
	
	private function procRequest()
	{	

		$pathUri = '';
		$pos = strpos($_SERVER['REQUEST_URI'],$_SERVER["SCRIPT_NAME"]);
		if( $pos !== FALSE)
		{
			$pathUri =substr($_SERVER['REQUEST_URI'],strlen($_SERVER["SCRIPT_NAME"]));
		}
		else
		{

			$pathUri =substr($_SERVER['REQUEST_URI'],strlen(dirname($_SERVER["SCRIPT_NAME"])));
		}
		
		if(strlen($pathUri) > 0)
		{
			if($pathUri[0] == '/')
				$pathUri=substr($pathUri, 1);
				
			$pathArray =explode('?',$pathUri);
			
			if($pathArray[0] == '')
			{
	
			}
			else
			{
				$this->pathInfo=explode('/',  $pathArray[0]);
				
				/*
				if(count($this->pathInfo) > 2)
				{
					throw new CSPPException("route url invalid",CError::ERR_URL_ROUTER);
				}
				*/
			}
		}

	}
	
	public function getParam($index)
	{
		if(count($this->pathInfo) < 3+$index)
		{
			return null;
		}
		else
		{
			return $this->pathInfo[2+$index];
		}
	}
	
	public function getParams()
	{
		if(count($this->pathInfo) > 2)
		{
			return array_slice($this->pathInfo, 2);
		}
		else
		{
			return array();
		}

	}
	
	private static function  toCamel($str,$class = false)
	{
		$len = strlen($str);
		$out = '';
		for($i=0;$i<$len;++$i)
		{
			$ch = $str[$i];
			if($class && $i==0)
			{
				$out.=strtoupper($ch);
			}
			else
			{
				if($ch == '_')
				{
					$i++;
					$out.=strtoupper($str[$i]);
				}
				else
				{
					$out.=$ch;
				}
			}
		}
		return $out;
	}
	
	public function getAction()
	{
		if(count($this->pathInfo) == 1)
		{
			return $action='indexAction';
		}
		else if(count($this->pathInfo) >= 2)
		{
			if($this->pathInfo[1] == '')
				return 'indexAction';
			return $action = self::toCamel($this->pathInfo[1]).'Action';
		}
	}
	
	public function getController()
	{
		if($this->pathInfo[0] == '') 
			return 'CIndexController';
		$conn = 'C'.self::toCamel($this->pathInfo[0],true).'Controller';
		return $conn;
	}
}

class CSPPException extends ErrorException
{
	
}

class CError
{
	const ERR_NOT_FOUND_CLASS = 1001;
	const ERR_NOT_FOUND_METHOD = 1002;
	const ERR_URL_ROUTER = 1003;
}


