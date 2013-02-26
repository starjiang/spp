<?php
class CShmHashMap
{
	private $shmId = null;
	private $errmsg = '';
	private $head = null;
	
	static public function hash($key)
	{
		$h = 0;
		$key_len = strlen($key);
		for($idx = 0; $idx < $key_len; ++$idx)
		{
			$h = 11 * $h + ord($key[$idx]);
			if($h > 4294967295)
				$h &= 0x0FFFFFFFF;
		}
		return $h;
	}
	
	
	public function __destruct()
	{
		if($this->shmId !== null)
		{
			shmop_close($this->shmId);
		}
		
	}
	
	public function create($key,$buckets=12281,$size=10000000)
	{
		
		if($key == 0)
		{
			$this->errmsg = "key is 0";
			return false;
		}
		
		$this->shmId = shmop_open($key,'a',0,0);
		
		if($this->shmId !== false)
		{
			if(!shmop_delete($this->shmId))
			{
				$this->errmsg = 'delete exist shm error';
				return false;
			}
		}
		
		$this->shmId = shmop_open($key,'c',0777,$size);
		if($this->shmId === false)
		{
			$this->errmsg = 'create shm error';
			return false;
		}
		
		$head['bsize'] = $size;
		$head['size'] = 0;
		$head['buckets'] = $buckets;
		$head['start'] = 24;
		$head['data'] = $buckets*8+24;
		$head['free'] = $buckets*8+24;
		
		return $this->setHead($head);

		
	}
	
	public function init($key)
	{
		
		if($this->shmId != null)
		{
			return true;
		}
		
		if($key == 0)
		{
			$this->errmsg = "key is 0";
			return false;
		}
		
		$this->shmId = shmop_open($key,'a',0,0);

		if($this->shmId === false)
		{
			$this->errmsg = "open ".$key." shm error";
			return false;
		}
		
		return $this->getHead();
	}
	
	public function getErrMsg()
	{
		return $this->errmsg;
	}
	
	
	private function getHead()
	{
		
		$data = shmop_read($this->shmId,0,24);
		
		if($data === false)
		{
			$this->errmsg = 'read head error';
			return false;
		}
		
		$this->head = unpack("Ibsize/Isize/Ibuckets/Istart/Idata/Ifree", $data);
		return $this->head;
	}
	private function setHead($head)
	{
		$this->head = $head;
		
		$data =pack('IIIIII',$head['bsize'],$head['size'],$head['buckets'],$head['start'],$head['data'],$head['free']);
		
		if(!shmop_write($this->shmId,$data,0))
		{
			$this->errmsg = 'write head error';
			return false;
		}
		return true;
	}
	public function set($key,$value)
	{
		$head = $this->head;
		$intKey = CShmHashMap::hash($key);
		
		$data = array();
		$data['key'] = $key;
		$data['value'] = $value;
		
		$wbuf = json_encode($data);
		$wlen = strlen($wbuf)+8;
		
		if($head['free']+$wlen > $head['bsize'])
		{
			$this->errmsg = 'no memory';
			return false;
		}		
		$index  = $intKey % $head['buckets'];
		$buf = shmop_read($this->shmId,$head['start']+(8*$index),8);
		
		$anext = unpack('I2',$buf);
		
		$next = $anext[1];
		$nlen = $anext[2];
		
		$offset = 0;
		
		if($next == null || $next == 0)
		{
			$next = $head['free'];
			$nlen = $wlen;
			$offset = $head['start']+(8*$index);
		}
		else 
		{

			$node = array();
						
			while($next!=null && $next !=0)
			{
				$buf = shmop_read($this->shmId,$next,$nlen);
				$offset = $next;
				
				$adata = unpack('I2next/a*data', $buf);
				
				$next = $adata['next1'];
				$nlen = $adata['next2'];
				$node =json_decode($adata['data'],true);
				
				if($node['key'] == $key)
				{
					$this->errmsg = $key.' exsit';
					return false;
				}
			}
			
			$next=$head['free'];
			$nlen=$wlen;
	
		}

		if(!shmop_write($this->shmId,pack('IIa*',0,0,$wbuf),$head['free']))
		{
			$this->errmsg = 'write node error';
			return false;
		}
		
		if(!shmop_write($this->shmId,pack('II',$next,$nlen),$offset))
		{
			$this->errmsg = 'write nlen error';
			return false;
		}
		
		$head['free']+=$wlen;
		$head['size']++;
		return $this->setHead($head);
		
	}
	
	public function get($key)
	{

		if($this->shmId == null)
		{
			$this->errmsg = 'shm not init';
			return false;
		}
		
		$head = $this->head;
		
		$intKey = CShmHashMap::hash($key);
			
		$index  = $intKey % $head['buckets'];
	
		$buf = shmop_read($this->shmId,$head['start']+(8*$index),8);
		
		$anext = unpack('I2',$buf);

		$next = $anext[1];
		$nlen = $anext[2];
		$node = array();
		
		if($next == null || $next == 0)
		{
			$this->errmsg = 'no data';
			return false;
		}
		else
		{
		
			while($next!=null && $next !=0)
			{
				$buf = shmop_read($this->shmId,$next,$nlen);
				$adata = unpack('I2next/a*data', $buf);
				
				$next = $adata['next1'];
				$nlen = $adata['next2'];
				$node =json_decode($adata['data'],true);
				if($node['key'] == $key)
				{
					return $node['value'];
				}
				
			}
		}
		$this->errmsg = 'not found';
		return false;
	}
}
