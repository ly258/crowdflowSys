<?php
require_once '../include.php';
$act=$_REQUEST['act'];

if($act=="getServerName")
{
    print_r(getServerName($link));
}