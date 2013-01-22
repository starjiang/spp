<?php
interface  ISource
{
	public function set($key,$val);
	public function get($key);
	public function delete($key);
}