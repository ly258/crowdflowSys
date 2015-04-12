<?php
require_once '../include.php';
$act=$_REQUEST['act'];

switch ($act)
{
    case "getServerName":
        print_r(getServerName($link));
        break;
    case "startServer":
        $ip = $_REQUEST['ip'];
        $port = $_REQUEST['port'];
        $connection = array("ip"=>$ip,"port"=>$port);
        $result = serverCommand($connection,"start");
        echo $result;
        break;
    case "stopServer":
        $ip = $_REQUEST['ip'];
        $port = $_REQUEST['port'];
        $connection = array("ip"=>$ip,"port"=>$port);
        $result = serverCommand($connection,"stop");
        echo $result;
        break;
    case "restartServer":
        $ip = $_REQUEST['ip'];
        $port = $_REQUEST['port'];
        $connection = array("ip"=>$ip,"port"=>$port);
        $result = serverCommand($connection,"restart");
        echo $result;
        break;
    case "addServer":
        $ip = $_REQUEST['ip'];
        $port = $_REQUEST['port'];
        $sql = "insert into videocms_server (serverip,port) values ('".$ip."',".$port.")";
        if(!query($link, $sql))
        {
            echo "addFailed";
        }else{
            echo "addSuccess";
        }
        break;
    case $act=="testServer":
        $ip = $_REQUEST['ip'];
        $port = $_REQUEST['port'];
        $connection = array("ip"=>$ip,"port"=>$port);
        $result = serverCommand($connection,"ping");
        echo $result;
        break;
    case $act=="deleteServer":
        $ip = $_REQUEST['ip'];
        $port = $_REQUEST['port'];
        $connection = array("ip"=>$ip,"port"=>$port);
        $result = serverCommand($connection,"stop");
        $sql = "delete from videocms_server where serverip='".$ip."'";
        if($result=="stoped"&&query($link, $sql))
            echo "删除成功<br/><a href='serverManager.php'>返回</a>";
}