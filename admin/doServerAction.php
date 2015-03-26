<?php
require_once '../include.php';
$act=$_REQUEST['act'];

if($act=="getServerName")
{
    print_r(getServerName($link));
}else if($act=="startServer")
{
    $serverip = $_REQUEST['serverip'];
    $port = $_REQUEST['port'];
    $connection = array("serverip"=>$serverip,"port"=>$port);
    serverCommand($connction,"start");
}