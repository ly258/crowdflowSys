<?php 
require_once '../include.php';
checklogined();

$id=$_GET['id'];
if(!$id)
{
    die("非法ID");
}

$sql="select fromtime,endtime,tilt,pan,focal from videocms_reset where id='".$id."'";
$result=fetchOne($link, $sql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<h3>编辑预置位</h3>
<form action="doAdminAction.php?act=editReset&id=<?php echo $id;?>" method="post">
<table width="70%" border="1" cellpadding="5" cellspacing="0" bgcolor="#cccccc">
	<tr>
		<td align="right">开始时间</td>
		<td><input type="text" name="fromtime" value="<?php echo $result['fromtime'];?>" /></td>
	</tr>
	<tr>
		<td align="right">结束时间</td>
		<td><input type="text" name="endtime" value="<?php echo $result['endtime'];?>" /></td>
	</tr>
	<tr>
		<td align="right">俯仰角</td>
		<td><input type="text" name="tilt" value="<?php echo $result['tilt'];?>" /></td>
	</tr>
	<tr>
		<td align="right">方位角</td>
		<td><input type="text" name="pan" value="<?php echo $result['pan'];?>" /></td>
	</tr>
	<tr>
		<td align="right">焦距</td>
		<td><input type="text" name="focal" value="<?php echo $result['focal'];?>" /></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit"  value="编辑预置位"/></td>
	</tr>

</table>
</form>
</body>
</html>