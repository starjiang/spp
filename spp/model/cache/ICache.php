<?php
namespace spp\model\cache;
interface ICache
{
	public function set($key,$val,$expire);
	public function mget($keys);
	public function get($key);
	public function delete($key);
	
}