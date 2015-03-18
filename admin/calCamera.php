<?php
require_once '../include.php';
checklogined();

function goback(){
    echo '非法访问';
    echo '<a href="listCamera.php">返回</a>';
    exit();
}
if(!array_key_exists('id',$_GET)){
    goback();
}
$id = $_GET['id'];
$sql = "select * from videocms_camera where id = '$id'";
//echo $sql;
$result = pg_query($link,$sql);
if(pg_num_rows($result)<1){
    goback();
}
$row = pg_fetch_array($result);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>摄像机标定</title>
        <link href="css/main.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="./scripts/jquery-1.11.2.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
	<script type="text/javascript" src="js/OpenLayers.js"></script>
    </head>
    <body>
<div style="text-align:center">
<table border="0" class="maintable">
<tr>
	<td class="maintd">
		<div class="maptopblank"></div>
		<div id="map" class="smallmap"></div>
	</td>
	<td rowspan="3">&nbsp;&nbsp;</td>
	<td rowspan="3"class="maintd2">
		<div class="container">
		    <canvas id="panel" width="800" height="533"></canvas>
		</div>
	</td>
</tr>
<tr>
	<td style="height: 100px">
		<div id="info" class="info">
        CCD尺寸:<input type="text" id="ccd1" class="input" value="<?php echo $row['ccd_width']; ?>" />*<input type="text" id="ccd2" class="input" value="<?php echo $row['ccd_height']; ?>" /><br/>
        焦距:<input type="text" id="f" class="input" value="<?php echo $row['focal']; ?>" />最大可见距离:<input type="text" id="maxl" value="120" class="input" /><br />
                预计最大高度:<input type="text" id="maxh" value="20" class="input" />预计最小高度:<input type="text" id="minh" value="3" class="input" /><br />
                预计最大俯角:<input type="text" id="maxt" value="80" class="input" />预计最小俯角:<input type="text" id="mint" value="20" class="input" /><br />
                <input type="hidden" id="row_id" value="<?php echo $row['id']; ?>" />

                <div id="info2"></div></div>
	</td>
</tr>
<tr>
	<td>
		<input type="file" name="doc" id="doc" onchange="javascript:setImagePreview();" />
		<input type="button" name="addClick" value="&nbsp;添加一个新点&nbsp;" onClick="javascript:addArr();" />
		<input type="button" name="addClick" value="&nbsp;&nbsp;&nbsp;&nbsp;标&nbsp;&nbsp;定&nbsp;&nbsp;&nbsp;&nbsp;" onClick="javascript:send();" />
		<input type="button" name="addClick" value="&nbsp;&nbsp;录入数据库&nbsp;&nbsp;" onClick="javascript:save();" />
	</td>
</tr>
</table>
</div>	
        
        <footer>
            <h2>VideoGIS - 摄像机标定</h2>
        </footer>
    </body>
</html>
