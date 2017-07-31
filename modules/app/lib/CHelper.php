<?php
class CHelper
{
	static public function showMessage($msg)
	{
		echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><script>alert('".$msg."')</script>";
	}
	static public function goUrl($url)
	{
		echo "<script>location.href = '".$url."'</script>";
	}
	
	static public function goParentUrl($url)
	{
		echo "<script>parent.location.href = '".$url."'</script>";
	}
	
	static public function goBack()
	{
		echo "<script>history.back()</script>";
	}
	
	static public function cutUtf8Str($str, $from, $len)
	{
		return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s','$1',$str);
	}
	
	static public function checkLogin()
	{
		session_start();
		if(isset($_SESSION['login_name'])&& isset( $_SESSION['login_auth']))
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
}