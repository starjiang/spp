<?php
namespace spp\component;

class CShmHashMap
{
	private $shmId = null;
	private $errmsg = '';
	private $head = null;
	private static $avFlag = false;
	
	static public function hash($key)
	{
	    /*
	    $hash = 0;
	    $len = strlen($key);
	    for($i = 0; $i < $len; ++$i)
	    {
		    $hash = 33 * $hash + ord($key[$i]);
		    if($hash > 4294967295)
			    $hash &= 0x0FFFFFFFF;
	    }*/
	    return crc32($key);
	}
	
	public function __construct()
	{
		if(function_exists("av_encode"))
		{
			self::$avFlag = true;
		}
	}
	
	public function __destruct()
	{
		if($this->shmId != null)
		{
			@shmop_close($this->shmId);
		}
		
	}
	
	public function create($key,$buckets=12281,$size=10000000)
	{
		
		if($key == 0)
		{
			$this->errmsg = "key is 0";
			return false;
		}
		
		$this->shmId = @shmop_open($key,'a',0,0);
		
		if($this->shmId != false)
		{
			if(!shmop_delete($this->shmId))
			{
				$this->errmsg = 'delete exist shm 0x'.dechex($key).' error';
				return false;
			}
		}
		
		$this->shmId = shmop_open($key,'c',0777,$size);
		if($this->shmId == false)
		{
			$this->errmsg = 'create shm 0x'.dechex($key).' error';
			return false;
		}
		
		$head['bsize'] = $size;
		$head['size'] = 0;
		$head['buckets'] = $buckets;
		$head['start'] = 24;
		$head['data'] = $buckets*4+24;
		$head['free'] = $buckets*4+24;
		
		return $this->setHead($head);

		
	}
	
	public function attach($key)
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
		
		$this->shmId = @shmop_open($key,'a',0,0);

		if($this->shmId == false)
		{
			$this->errmsg = "attach 0x".dechex($key)." shm error";
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

                $vardata = self::encode($value);
                $newkeylen = strlen($key);
                $newvarlen = strlen($vardata);
                
                $wlen = 12+$newkeylen+$newvarlen;
                
		if($head['free']+$wlen > $head['bsize'])
		{
			$this->errmsg = 'no memory';
			return false;
		}
		$index  = $intKey % $head['buckets'];
		$buf = shmop_read($this->shmId,$head['start']+(4*$index),4);
		$anext = unpack('I',$buf);

		$next = $anext[1];
		$offset = 0;
		
		if($next == null || $next == 0)
		{
			$next = $head['free'];
			$offset = $head['start']+(4*$index);
		}
		else 
		{
			while($next!=null && $next !=0)
			{
				$buf = shmop_read($this->shmId,$next,12);
				$offset = $next;
				$adata = unpack('I3', $buf);
				$nnext = $adata[1];
				$keylen = $adata[2];
                                $varlen = $adata[3];

				$buf = shmop_read($this->shmId,$next+12,$keylen+$varlen);
                                $adata = unpack('a'.$keylen.'key/a'.$varlen."value", $buf);
                                $nextkey = $adata['key'];
				if($nextkey == $key)
				{
					$this->errmsg = $key.' exsit';
					return false;
				}
				$next = $nnext;
			}
			
			$next = $head['free'];
		}
		
		if(!shmop_write($this->shmId, pack('IIIa'.$newkeylen.'a'.$newvarlen,0,$newkeylen,$newvarlen,$key,$vardata),$head['free']))
		{
			$this->errmsg = 'write node error';
			return false;
		}

		if(!shmop_write($this->shmId,pack('I',$next),$offset))
		{
			$this->errmsg = 'write nlen error';
			return false;
		}
		
		$head['free']+=$wlen;
		$head['size']++;
		return $this->setHead($head);
		
	}
	
	public static function encode($data)
	{
		if(self::$avFlag)
		{
			return av_encode($data);
		}
		else
		{
			return CUtils::encode($data);
		}
	}
	
	public static function decode($data)
	{
		if(self::$avFlag)
		{
			return av_decode($data);
		}
		else
		{
			return CUtils::decode($data);
		}
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
	
		$buf = shmop_read($this->shmId,$head['start']+(4*$index),4);
		$anext = unpack('I',$buf);
		$next = $anext[1];
		
		if($next == null || $next == 0)
		{
			$this->errmsg = $key.' no data';
			return false;
		}
		else
		{
			while($next!=null && $next !=0)
			{
				$buf = shmop_read($this->shmId,$next,12);
				$adata = unpack('I3', $buf);
				
				$nnext = $adata[1];
                                $keylen = $adata[2];
                                $varlen = $adata[3];
                                
				$buf = shmop_read($this->shmId,$next+12,$keylen+$varlen);
                                
                                $adata = unpack('a'.$keylen."key/a".$varlen."value", $buf);

				if($adata['key'] == $key)
				{
                                    return self::decode($adata['value']);
				}
				$next = $nnext;
			}
		}
		$this->errmsg = $key.' not found';
		return false;
	}
}
