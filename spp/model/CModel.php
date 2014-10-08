<?php
abstract class CModel
{
	private $dirty = false;
	private $create = false;
	private $keyName = null;
	
	protected  function fields()
	{
		$caller = get_called_class();
		return $caller::$fields;
	}
	
	public function isCreate()
	{
		return $this->create;
	}
	
	public function isDirty()
	{
		return $this->dirty;
	}
	
	protected function setCreate($flag)
	{
		$this->create = $flag;
		return $this;
	}
	
	protected  function setDirty($flag)
	{
		$this->dirty = $flag;
		return $this;
	}
	
	protected  function keyName()
	{
		if(!isset($this->keyName) || $this->keyName == null)
		{
			$keys= array_keys($this->fields());
			$this->keyName = $keys[0];
		}
		return $this->keyName;
	}
	
	public function setKey($key)
	{
		$keyName = $this->keyName();
		$this->$keyName = $key;
		$this->dirty = true;
		return $this;
	}
	
	public function getKey()
	{
		$keyName = $this->keyName();
		return $this->$keyName;
	}
	
	public function fromArray($infos = null)
	{
		if( is_array($infos) || is_object($infos))
		{
			$fields = $this->fields();
			foreach($infos as $key => $var)
			{
				if(array_key_exists($key, $fields))
				{
					$this->$key = $var;
				}
			}
		}
		return $this;
	}

	public function toArray()
	{
		$infos=array();
		$fields = $this->fields();
		$keyName = $this->keyName();
		
		/*
		if(!$this->isCreate())
		{
			if($this->$keyName == null || $this->$keyName == '' || $this->keyName == 0 )
			{
				throw new CModelException('primay key field '.$this->$keyName.' is not set in '.get_class($this));
			}
		}
		*/
		foreach($fields as $key => $var)
		{
			if(is_int($var))
			{
				$infos[$key] = (int)$this->$key;
			}
			else
			{
				$infos[$key] = $this->$key;
			}
		}
		return $infos;
	}

	public function __call($m,$a)
	{
		$do =substr($m,0,3);
		if($do =='get')
		{
			$field = substr($m,3);
			$field = self::toUnix($field);
			return $this->$field;
		}
		else if ($do == 'set')
		{
			$field = substr($m,3);
			$field = self::toUnix($field);
			if($this->$field !==  $a[0])
			{
				$this->$field = $a[0];
				$this->dirty = true;
			}
			return $this;
		}
		else
		{
			throw new CModelException('can not find method '.$m.' in '.get_class($this));
		}
	}
	
	private static function toUnix($str)
	{
		$len = strlen($str);
		$out = '';
		for($i=0;$i<$len;++$i)
		{
			$ch = ord($str[$i]);
			if($ch>64 && $ch<91)
			{
				if($out != '')
				{
					$out.='_'.strtolower($str[$i]);
				}
				else 
				{
					$out.=strtolower($str[$i]);
				}
			}
			else 
			{
				$out.=$str[$i];
			}
		}
		return $out;
	}
	public function __get($field)
	{
		if(!array_key_exists($field,$this->fields()))
		{
			$field = self::toUnix($field);
		}
		
		if(array_key_exists($field,$this->fields()))
		{
			if(!isset($this->$field) || $this->$field === null)
			{
				$fields = $this->fields();
				$this->$field = $fields[$field];
			}
			return $this->$field;
		}
		else
		{
			throw new CModelException('can not find field '.$field.' in '.get_class($this));
		}

	}

	public function __set($field, $value)
	{
		$fields = $this->fields();
		
		if(!array_key_exists($field,$fields))
		{
			$field = self::toUnix($field);
		}
		
		if(array_key_exists($field,$fields))
		{
			if(!isset($this->$field) || $this->$field !== $value)
			{
				$defaultValue = $fields[$field];

				if(is_int($defaultValue))
				{
					$this->$field =(int)$value;
				}
				else 
				{
					$this->$field = (string)$value;
				}
				$this->dirty = true;
			}
		}
		else
		{
			throw new CModelException('can not find field '.$field.' in '.get_class($this));
		}
		return $this;

	}

	
	public static function model()
	{
		$caller = get_called_class();
		return new $caller();
	}
	
	public static function add()
	{
		$caller = get_called_class();
		$obj = new $caller();
		$obj->setCreate(true);
		return $obj;
	}
	
	abstract public  function save();

	abstract public  function get($key);
	
	abstract public  function delete($key);
	
}



