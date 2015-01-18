<?php
require_once '../include.php';
$act=$_REQUEST['act'];

if($act=="logout")
{
    logout();
}elseif ($act=="addAdmin")
{
    $mes=addAdmin($link);
}elseif ($act=="editAdmin")
{
    $id=$_REQUEST['id'];
    $mes=editAdmin($link,$id);
}elseif ($act=="delAdmin")
{
    $id=$_REQUEST['id'];
    $mes=delAdmin($link,$id);
}elseif($act=="addCamera")
{
    $mes=addCamera($link);
}elseif($act=="delCamera")
{
    $id=$_REQUEST['id'];
    $mes=delCam($link, $id);
}elseif($act=="editCamera")
{
    $id=$_REQUEST['id'];
    $mes=editCamera($link,$id);
}elseif($act=="addReset")
{
    $cid=$_REQUEST['cid'];
    $mes=addReset($link,$cid);
}elseif($act=="delReset")
{
    $id=$_REQUEST['id'];
    $mes=delReset($link,$id);
}elseif($act=="editReset")
{
    $id=$_REQUEST['id'];
    $arr=$_POST;
    $mes=updateReset($link,$id,$arr);
}elseif ($act=="setcoorsystem")
{
    $arr=$_POST;
    $mes=setcoorsystem($link,$arr);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
    <?php
    if($mes)
    {
        echo $mes;
    }
    ?>
    </body>
</html>