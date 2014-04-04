<?php
abstract class CLBDBModel extends CModel
{
	abstract  protected function pdos();
	//add by voice hu  废弃路由实现接口，当有废弃路由时需要在子类中实现
	protected function oldPdos()
	{
		return false;
	}
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}
	
	//add by voice hu  废弃路由表名称计算逻辑，当路由更换时需要在子类中重写
	protected function oldPrefix()
	{
		return false;
	}
	
	//该方法暂时无用
	private function getIdList($keys)
	{
		$newKeys = array();
		foreach ($keys as $key)
		{
			$newKeys[] = "'".$key."'";
		}
		return implode(',', $newKeys);
	}
	
	//单库操作返回第零个库，多库操作时需要修改该方法
	private  function getIndex($key)
	{
		return 0;
	}
	
	public function save()
	{
		$pdos = $this->pdos();
		$sth = $pdos[0]->prepare('replace into '.$this->prefix().' (id,val) values (:key,:value)');
		
		if(!$sth)
		{
			$error=$pdos[0]->errorInfo();
			throw new CModelException($error[2]);
		}
		if($sth->execute(array('key'=>$this->getKey(),'value'=>json_encode($this->toArray()))) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
		//add by voice hu
		return true;
	}
	
	public function delete($key)
	{
		$this->setKey($key);
		$pdos = $this->pdos();
		
		$sth =$pdos[0]->prepare('delete from '.$this->prefix().' where id = :key');
		
		if(!$sth)
		{
			$error=$pdos[0]->errorInfo();
			throw new CModelException($error[2]);
		}
				
		if($sth->execute(array('key'=>$key)) === false)
		{
			$error=$sth->errorInfo();
			throw new CModelException($error[2]);
		}
		return true;
	}
	
	//add by voice hu
	//$pdos   数据库列表
	//$table  操作的表名称
	//$key    键值
	private function _get(&$pdos,$table,$key)
	{
		//则直接返回错误
		if(!is_array($pdos) || !$table)
		{
			return false;
		}
		//添加设置key操作
		$sth = $pdos[0]->prepare('select * from '.$table.' where id = :key');
		if(!$sth)
		{
			$error=$pdos[0]->errorInfo();
			throw new CModelException($error[2]);
		}
		//执行数据库
		if($sth->execute(array('key'=>$key)) === false)
		{
			$error = $sth->errorInfo();
			throw new CModelException($error[2]);
		}
		//获取返回数据，返回一个索引为结果集列名的数组
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		//关闭游标
		$sth->closeCursor();
		
		if(is_array($row))
		{
			$this->fromArray(json_decode($row['val'],true))->setDirty(false);
			return $this;
		}
		return false;
	}
	
	//update by voice hu
	public function get($key) 
	{
		 $this->setKey($key);
		 $pdos = $this->pdos();
		 $table = $this->prefix();
		 $result = $this->_get($pdos, $table, $key);
		 //如果从正式路由中取数据失败，则从老路由中继续取数据
		 if(!$result)
		 {
		 	$oldpdos = $this->oldPdos();
		 	$table = $this->oldPrefix();
		 	$result = $this->_get($oldpdos, $table, $key);
		 	if(!$result)
		 	{
		 		return false;
		 	}
		 	//如果从废弃路由中取得数据，则保存至正式路由的数据库中
		 	$result->save();
		 	return $result;
		 }
		 return $result;
	}
	
	//多键查询 add by voice hu
	public static function mget($keys)
	{
		$objs = array();
		foreach ($keys as $k)
		{
			$caller = get_called_class();
			$callerObj = new $caller();
			$result = $callerObj->get($k);
			if($result)
			{
				$objs[$k] = $result;
			}
		}
		if(count($objs) == 0)
		{
			return false;
		}
		
		return $objs;
	}

	public function IsNumStr($str)
	{
		if(!$str) return false;
		if(eregi('^[0-9]*$',$str))
			return true;
		else
			return false;
	}
}

