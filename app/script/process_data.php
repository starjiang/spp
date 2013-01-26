<?php
include 'init.php';

if(count($argv) < 2)
{
	echo "usage:".$argv[0]." model \n";
	return;
}



$model = $argv[1];

$info = CCReader::get('cfg.services.mem.'.$model);

$redis = CConnMgr::init()->getRedis($info['modify_host'],$info['modify_port']);

$output = '';

for($i=0;$i<$info['modify_buckets'];$i++)
{
	$list = $redis->sMembers($info['modify_prefix']."_".$i);
	
	foreach($list as $id)
	{
		$data = $model::model()->get($id);
		var_dump($data);
		$output.=$id."\t".json_encode($data)."\n";
	}
}

file_put_contents('data/'.$model."_".time(), $output);
