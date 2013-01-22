<?php

if(count($argv)<3)
{
	echo "usage:".$argv[0]." path outfile [exclude]\n";
	return;
}

$dir=$argv[1];
$outfile = $argv[2];
$exclude=$argv[3];

$exclude = explode(',', $exclude);

array_push($exclude,$outfile);

$files = scandir($dir);

if($files === false)
{
	echo "path is invalid\n";
	return;
}

$output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<cfg>";


foreach($files as $file)
{

	if(substr($file,-4,4) == '.xml' && array_search($file,$exclude)=== false)
	{
		$xml = simplexml_load_file($dir."/".$file);
		if($xml)
		{
			if($xml->getName() == 'cfg' || $xml->getName().".xml" !== $file)
			{
				echo $file." root node is cfg or not equal file name\n";
				continue;
			}
		}
		else
		{				
			echo $file." can not prased\n";
			continue;
		}
		
		echo $file."\n";

		$contents= file_get_contents($dir."/".$file);
		$output.= preg_replace('/<\?xml .*\?>/', '', $contents);
	}
}

if($output !='')
{
	$output.="\n</cfg>";
	
	file_put_contents($dir."/".$outfile, $output);
	echo "merge ok\n";
}



