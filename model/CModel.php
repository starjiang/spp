<?php
abstract class CModel
{

	private $dirty = false;
	
	abstract protected  function fields();
	
	public function isDirty()
	{
		return $this->dirty;
	}
	
	protected  function keyName()
	{
		$keys = $this->fields();
		return $keys[0];
	}
	
	public function setKey($key)
	{
		$keyName = $this->keyName();
		$this->$keyName = $key;
		return $this;
	}
	
	public function fromArray($infos = null)
	{
		if( is_array($infos) || is_object($infos))
		{
			foreach($infos as $key => $val)
			{
				$fields = $this->fields();
				if(array_search($key,$fields)!==FALSE)
				{
					$this->$key = $val;
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
		
		if($this->$keyName == null )
		{
			throw new ErrorException('primay key field '.$this->$keyName.' is not set in '.get_class($this));
		}
		
		foreach($fields as $key)
		{
			$infos[$key] = $this->$key;
		}
		return $infos;
	}

	public function __call($m,$a)
	{
		$do =substr($m,0,3);
		if($do =='get')
		{
			$field = substr($m,3);
			$field[0]=strtolower($field[0]);
			
			if(array_search($field,$this->fields())!==FALSE)
			{
				return $this->$field;
			}
			else
			{
				throw new ErrorException('can not find field '.$field.' in '.get_class($this));
			}
			
		}
		else if ($do == 'set')
		{
			$field = substr($m,3);
			$field[0]=strtolower($field[0]);
			if(array_search($field,$this->fields())!==FALSE)
			{
				$this->$field = $a[0];
				return $this;
			}
			else
			{
				throw new ErrorException('can not find field '.$field.' in '.get_class($this));
			}
		}
		else
		{
			throw new ErrorException('can not find method '.$m.' in '.get_class($this));
		}
	}
	
	public function __get($field)
	{
		if(array_search($field,$this->fields())!==FALSE)
		{
			return $this->$field;
		}
		else
		{
			throw new ErrorException('can not find field '.$field.' in '.get_class($this));
		}

	}

	public function __set($field, $value)
	{
		if(array_search($field,$this->fields())!==FALSE)
		{
			if($this->$field != $value)
			{
				$this->$field = $value;
				$this->dirty = true;
			}

		}
		else
		{
			throw new ErrorException('can not find field '.$field.' in '.get_class($this));
		}
		return $this;

	}

	public static function model()
	{
		$caller = get_called_class();
		return new $caller();
	}
	
	
	abstract public  function save();

	abstract public  function get($key);
	
	abstract public  function delete($key);
	
}

