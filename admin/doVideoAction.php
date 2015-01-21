<?php
include_once("../include.php");
include_once("../core/video.inc.php");

$action = $_REQUEST["action"];
$videoDB = new VideoDB($link);
//添加视频
if($action == "addVideo"){
	 //print_r($_POST);
	 $videoItem = new Video();
	 $videoItem->name = $_POST["name"];
	 $videoItem->url =  $_POST["file"];
	 $videoItem->ccdwidth = $_POST["ccd_width"];
	 $videoItem->ccdheight = $_POST["ccd_height"];
	 $videoItem->startTime = strtotime($_POST['startTime']);
	 $videoItem->endTime = strtotime($_POST['endTime']);
	 $relate = (empty($_POST['relate'])) ? 0:$_POST['relate'];
	 $camera = getCameraByID($relate);
	 $videoItem->statesfromJSON($_POST['statesJson']);
	 if(!empty($camera)){
	 	$videoItem->setRelateCamera($camera);
	 }
	 
	 if($videoDB->add($videoItem)){
	 	$mes="添加成功！<br/><a href='videoCtr.php'>继续添加</a>|<a href='listVideo.php'>查看视频列表</a>";
	 }else{
	 	$mes="添加失败！<br/><a href='videoCtr.php'>继续添加</a>|<a href='listVideo.php'>查看视频列表</a>";
	 }
	 
}else if($action == "delVideo"){
	 if($videoDB->remove($_GET["id"])){
	 	$mes="删除成功！<br/><a href='listVideo.php'>查看视频列表</a>";
	 }else{
	 	$mes="删除失败！<br/><a href='listVideo.php'>查看视频列表</a>";
	 }
}else if($action == "updateVideo"){
	 $videoItem = new Video();
	 $videoItem->id = $_POST["id"];
	 $videoItem->name = $_POST["name"];
	 $videoItem->url =  $_POST["file"];
	 $videoItem->ccdwidth = $_POST["ccd_width"];
	 $videoItem->ccdheight = $_POST["ccd_height"];
	 $videoItem->startTime = strtotime($_POST['startTime']);
	 $videoItem->endTime = strtotime($_POST['endTime']);
	 $relate = (empty($_POST['relate'])) ? -1 :$_POST['relate'];
	 $camera = getCameraByID($relate);
	 $videoItem->statesfromJSON($_POST['statesJson']);
	 if(!empty($camera)){
	 	$videoItem->setRelateCamera($camera);
	 }
	 
	 if($videoDB->update($videoItem)){
	 	$mes="修改成功！<br/><a href='listVideo.php'>查看视频列表</a>";
	 }else{
	 	$mes="修改失败！<br/><a href='listVideo.php'>查看视频列表</a>";
	 }
	
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