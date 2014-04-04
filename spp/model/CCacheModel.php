<?php
abstract class CCacheModel extends CModel
{
	
	abstract protected  function cache();
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}
	
	protected function persist()
	{
		return false;
	}
	
	protected function source()
	{
		return null;
	}
	
	protected function delayWrite()
	{
		return false;
	}
	
	protected function modifyList()
	{
		return null;
	}
	
	public  function getNKey()
	{
		$keyName = $this->keyName();
	
		if($this->$keyName == null || $this->$keyName == '')
			throw new ErrorException('the primay key field '.$keyName.' in '.get_class($this).' not setted');
	
		if($this->prefix() != '')
			return $this->prefix()."_".$this->$keyName;
		else
			return strtolower(get_class($this))."_".$this->$keyName;
	}
	
	//定义固化数据方法，该方法可以在子类中重写
	public function solidify()
	{
		$data = $this->modifyList()->getAndDel();//取出数据，落地到mysql中
		foreach ($data as $key=>$value)
		{
			$val = $this->getFromMc($value);
						
			if($val != false && $this->source())	
			{
				$this->source()->set($value,$val);
			}
			else 
			{
				$this->modifyList()->push($value);//保存数据，方便回归
			}
		}
	}
			
	public function save()
	{
		$keyName=$this->keyName();
		$nskey = $this->getNKey();
		$val = $this->toArray();
		//需要持久化的数据
		if($this->persist())
		{
			if($this->delayWrite()) //缓写支持
			{
				//推入缓存
				if(!$this->cache()->set($nskey,json_encode($val)))
				{
					throw new CModelException('save cache fail in '.get_class($this).' key:'.$nskey.' value:'.json_encode($val));
					return false;
				}
				//如果数据写入成功则进行缓写
				if($this->modifyList()) 
				{ 
					$this->modifyList()->push($this->$keyName);
				}
				else
				{
					throw new CModelException('modifyList is null in '.get_class($this));
				}
			}
			else//不需要缓写则直接写入源数据库
			{
				$saveCacheSuc = true;
				
				if(!$this->cache()->set($nskey,json_encode($val)))
				{
					//写入缓存失败，则继续写入db之后抛出异常
					$saveCacheSuc = false;
				}
				
				if($this->source())	
				{
					$this->source()->set($this->$keyName,$val);
				}
				else
				{
					throw new CModelException('source is null in '.get_class($this));
				}
				
				if(!$saveCacheSuc)
				{
					throw new CModelException('save cache fail in '.get_class($this).' key:'.$nskey.' value:'.json_encode($val));
				}
			}
		}
		else 
		{
			if(!$this->cache()->set($nskey,json_encode($val)))
			{
				throw new CModelException('save cache fail in '.get_class($this).' key:'.$nskey.' value:'.json_encode($val));
			}
		}
	}
	
	public function getFromMc($key)
	{
		$this->setKey($key);
		$var = $this->cache()->get($this->getNKey());
		if($var !== false)
		{
			return json_decode($var,true);
		}
		return false;
	}

	public function get($key)
	{
		$this->setKey($key);
		
		$var = $this->cache()->get($this->getNKey());
		if($var !== false)
		{
			$this->fromArray(json_decode($var,true))->setDirty(false);
			return $this;
		}
		else
		{
			if($this->source()) 
			{
				$var = $this->source()->get($key);
				if($var !== false)
				{
					$this->fromArray($var)->setDirty(false);
					$this->cache()->set($this->getNKey(),json_encode($var));
					return $this;
				}
				$this->setDirty(false);
				return false;
			}
			$this->setDirty(false);
			return false;
		}
	}
	
	public static function mget($keys)
	{
		$caller= get_called_class();
		$callerObj = new $caller();
			
		$objs = array();
		$nsKeys = array();
		for($i=0; $i < count($keys); $i++)
		{
			$obj=new $caller();
			$obj->setKey($keys[$i]);
			$objs[$keys[$i]] = $obj;
			$nsKeys[$obj->getNKey()]=$keys[$i];
		}
			
		$vars = $callerObj->cache()->get(array_keys($nsKeys));

		if($vars !== false && count($vars) > 0 )
		{	
			foreach ($vars as $key =>$var)
			{
				$objs[$nsKeys[$key]]->fromArray(json_decode($var,true))->setDirty(false);
				
				unset($nsKeys[$key]);
			}
			if( count($vars) < count($keys) ) //内存中只有部分KEY时，从源里面取
			{
				if($callerObj->source()) //只有当源存在时才取
				{
					//从源头中获取剩余下没取到的key
					$vars = $callerObj->source()->get(array_values($nsKeys));
					if( $vars !== false)
					{
						foreach ($vars as $key =>$var)
						{
							$objs[$key]->fromArray($var)->setDirty(false);
							$callerObj->cache()->set($objs[$key]->getNKey(),json_encode($var));//写回memcached
							unset($nsKeys[$objs[$key]->getNKey()]);
						}
					}
				}

				foreach($nsKeys as $key => $value)
				{
					unset($objs[$value]);
				}
				return $objs;
			}
			else 
			{
				return $objs;
			}
		}
		else //取失败时从源里面取
		{
			if($callerObj->source())
			{
				$vars = $callerObj->source()->get($keys);
				if( $vars !== false)
				{
					foreach ($vars as $key =>$var)
					{
						$objs[$key]->fromArray($var);
						$callerObj->cache()->set($objs[$key]->getNKey(),json_encode($var));//写回memcached
					}
					$diffKeys=array_diff($keys,array_keys($vars));
					foreach($diffKeys as $key)
					{
						unset($objs[$key]);
					}
					return $objs;
				}
			}
			return false;
		}
	}

	public function delete($key)
	{
		$this->setKey($key);
		
		$this->cache()->delete($this->getNKey());
//		{
		if($this->persist())
		{
			if($this->source())	return $this->source()->delete($key);
		}
		return true;
//		}
//		return false;
	}

}

