<?php
    require_once '../core/getServer.php';
    /*$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_connect($sock,"127.0.0.1", 8000);
    //socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
    $buf = "get videos";
    socket_write($sock,$buf,strlen($buf));
    $result = socket_read($sock,1024);
    //print_r($sock);
    echo $result;
    socket_close($sock); */
    $connection = array('ip'=>'127.0.0.1','port'=>'8000');
    $XML = serverCommand($connection,"start");
    print_r($XML);
?>