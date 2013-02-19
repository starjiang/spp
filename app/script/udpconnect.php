<?php

echo microtime();
$sock=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);



$data['msg']='我是杜蕾斯枯井大本营\u2345';
$data['module']=$argv[1];

$data= json_encode($data);
$data="123456";
echo $data;
$ret = socket_sendto($sock,$data,strlen($data),0,"127.0.0.1",5050);
echo microtime();
