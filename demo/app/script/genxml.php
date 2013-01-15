<?php
$content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tests>";
for($i=0;$i<10000;$i++)
{
	$content.="<test".$i.">asdadsdassssssssssssssssssssssssssssssssssssss</test".$i.">";
	
}

$content.="</tests>";

file_put_contents("tests.xml", $content);