<?php
class CShmHashMap
{
	private $shmId = null;
	
	public function init($key,$size=10000000)
	{
		if($key == 0)
		{
			return false;
		}
		
		$this->shmId = shm_attach($key,$size,0777);
		if($this->shmId === false)
		{
			return false;
		}
		return true;
	}
	
	public function get($key)
	{
		$intKey = crc32($key);
		$value = shm_get_var($this->shmId,$intKey);
		if($value !== false)
		{
			$ar = json_decode($value,true);
			foreach ($ar as $key => $var)
			{
				if ($key === $key)
					return $var;
			}
			return false;
		}
		return false;
	}
	
	public function set($key,$value)
	{
		$intKey = crc32($key);
		$ar = array();
		if(shm_has_var($this->shmId,$intKey))
		{
			$strVal = shm_get_var($this->shmId,$intKey);
			$ar = json_decode($strVal,true);
			$ar[$key] = $value;
		}
		else
		{
			$ar[$key] = $value;
		}
		return shm_put_var($this->shmId,$intKey,json_encode($ar));
	}
	
	public function exsit($key)
	{
		$intKey = crc32($key);
		$value = shm_get_var($this->shmId,$intKey);
		
		if($value !== false)
		{
			$ar = json_decode($value,true);
			foreach ($ar as $key => $var)
			{
				if ($key === $key)
					return true;
			}
			return false;
		}
		return false;
	}
	
	public function flush()
	{
		return shm_remove($this->shmId);
	}
	
	public function delete($key)
	{
		$intKey = crc32($key);
		$ar = array();
		if(shm_has_var($this->shmId,$intKey))
		{
			$strVal = shm_get_var($this->shmId,$intKey);
			$ar = json_decode($strVal,true);
			if(count($ar) < 2)
				return shm_remove_var($this->shmId,$intKey);
			
			unset($ar[$key]);
			return shm_put_var($intKey,json_encode($ar));
		}
		else
		{
			return true;
		}
	}
	
	public function __destruct()
	{
		if($this->shmId !== null)
		{
			shm_detach($this->shmId);
		}
	}
	
}