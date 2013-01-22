<?php
interface ICache
{
	public function set($key,$val);
	public function get($key);
	public function delete($key);
	
}