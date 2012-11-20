<?php
abstract class CLBMongoModel extends CModel
{

	abstract protected  function mongodbs();
	
	protected  function prefix()
	{
		return strtolower(get_class($this));
	}
		
	private  function getIndex($key)
	{
		$fields = $this->fields();
		$defaultValue = $fields[$this->keyName()];
		$index = 0;
		if(is_int($defaultValue))
		{
			$index = (int)$key % count($this->mongodbs());
		}
		else
		{
			$intKey= crc32($defaultValue);
			$index = $intKey % count($this->mongodbs());
		}
		return $index;
	}
	
	public  function save()
	{

		$var = $this->toArray();
		
		if($this->keyName() != '_id')
		{
			throw new ErrorException('mongodb keyname must be [_id] in '.get_class($this));
		}
		
		
		$collection = $this->prefix();
		$mongodbs = $this->mongodbs();
		
		return $mongodbs[$this->getIndex($this->getKey())]->$collection->save($var);
	}

	public function get($key)
	{
		 $this->setKey($key);
		 $collection = $this->prefix();
		 $var = null;
		 
		 $mongodbs = $this->mongodbs();

		 $var = $mongodbs[$this->getIndex($key)]->$collection->findOne(array('_id' => $key));
		 
		 if( $var === null)
		 {
		 	return false;
		 }
		 else
		 {
		 	$this->fromArray($var)->setDirty(false);
		 	return $this;
		 }

	}
	
	public static function mget($keys)
	{
		$caller = get_called_class();
		$callerObj =  new $caller();
		
		$collection = $callerObj->prefix();

		$mongodbs = $callerObj->mongodbs();
		
		$indexKeys = array();
		
		foreach($keys as $key)
		{
			$indexKeys[$callerObj->getIndex($key)][]=$key;
		}
		
		$objs = array();

		foreach($indexKeys as $index =>$ikeys)
		{
			$results = $mongodbs[$index]->$collection->find(array('_id' => array('$in' => $ikeys)));
			
			foreach($results as $result)
			{
				$obj=new $caller();
				$obj->setKey($result['_id']);
				$obj->fromArray($result)->setDirty(false);
				$objs[$result['_id']] = $obj;
			}
		}
		return $objs;
	}

	public function delete($key)
	{
		$collection = $this->prefix();
		$mongodbs = $this->mongodbs();
		
		return $mongodbs[$this->getIndex($key)]->$collection->remove(array('_id' => $key));
	}

}

