<?php
require_once '../include.php';
checklogined();

$location = "ST_GeomFromText('POINT({$_GET['x']} {$_GET['y']})')";
$sql = "update videocms_camera set location=$location , ccd_width={$_GET['ccd1']} , ccd_height = {$_GET['ccd2']} , pan = 180+{$_GET['p']} , tilt = {$_GET['t']} , height = {$_GET['h']} , focal = {$_GET['f']} where id = '{$_GET['id']}';";

    if(query($link, $sql))
    {
        $mes="修改成功！<br/><a href='listCamera.php?page=1'>查看摄像机列表</a>";
    }else
    {
        $mes="修改失败！<br/><a href='listCamera.php?page=1'>查看摄像机列表</a>";
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
