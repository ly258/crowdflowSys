<?php
require_once '../include.php';
$act=$_REQUEST['act'];

if($act=="getServerName")
{
    print_r(getServerName($link));
}else if($act=="startServer")
{
    $ip = $_REQUEST['ip'];
    $port = $_REQUEST['port'];
    $connection = array("ip"=>$ip,"port"=>$port);
    $result = serverCommand($connection,"start");
    echo $result;
}else if($act=="stopServer")
{
    $ip = $_REQUEST['ip'];
    $port = $_REQUEST['port'];
    $connection = array("ip"=>$ip,"port"=>$port);
    $result = serverCommand($connection,"stop");
    echo $result;
}else if($act=="restartServer")
{
    $ip = $_REQUEST['ip'];
    $port = $_REQUEST['port'];
    $connection = array("ip"=>$ip,"port"=>$port);
    $result = serverCommand($connection,"restart");
    echo $result;
}else if($act=="addServer")
{
    $ip = $_REQUEST['ip'];
    $port = $_REQUEST['port'];
    $sql = "insert into videocms_server ";
}else if($act=="testServer")
{
    $ip = $_REQUEST['ip'];
    $port = $_REQUEST['port'];
    $connection = array("ip"=>$ip,"port"=>$port);
    $result = serverCommand($connection,"ping");
    echo $result;
}