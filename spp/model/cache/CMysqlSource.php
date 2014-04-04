<?php
class CMysqlSource implements ISource
{
	private $model = null;
	
	public function __construct($model)
	{
		$this->model = $model;
	}
	
	public function get($key)
	{

		if(is_array($key))
		{
			$model = $this->model;
			return $model::mget($key);
		}
		else 
		{
			$model = $this->model;
			return $model::model()->get($key);
		}
	
	}

	
	public function set($key,$val)
	{
		$model = $this->model;
		return $model::model()->setKey($key)->fromArray($val)->save();
	}
	
	public function delete($key)
	{
		$model = $this->model;
		return $model::model()->delete($key);
	}
}
