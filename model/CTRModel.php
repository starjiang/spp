<?php
abstract class CTRModel extends CModel
{
	abstract protected  function from();
	abstract protected  function to();
	
	public function save()
	{
		$to = $this->to();
		return $to::model()->fromArray($this)->save();

	}

	public function get($key)
	{
		$to = $this->to();
		$from = $this->from();
		$data = $to::model()->get($key);
		
		if($data === false)
		{
			$data = $from::model()->get($key);
			if($data)
			{
				$to::model()->fromArray($data)->save();
			}
			else
			{
				return false;
			}
		}
		return $this->fromArray($data);
	}

	public function delete($key)
	{
		$to = $this->to();
		$from = $this->from();
		
		if($to::model()->delete($key) && $from::model()->delete($key))
		{
			return true;
		}
		return false;
	}
	
	public static function mget($keys)
	{
		$caller= get_called_class();
		$callerObj= new $caller();
		$objs = array();
		
		$to = $callerObj->to();
		$from = $callerObj->from();
		
		$vars = $to::mget($keys);
				
		if(count($vars) > 0)
		{
			foreach ($vars as $key =>$var)
			{
				$obj=$caller::model()->fromArray($var)->setDirty(false);
				$objs[$key] = $obj;
			}
			
			$keys = array_diff($keys, array_keys($objs));
		
			if(count($keys) > 0)
			{
				$vars = $from::mget($keys);
				if(count($vars) > 0)
				{
					foreach ($vars as $key =>$var)
					{
						$obj=$caller::model()->fromArray($var)->setDirty(false);
						$objs[$key] = $obj;
						$to::model()->fromArray($var)->save();
					}
				}
			}
		}
		
		return $objs;
	}
}