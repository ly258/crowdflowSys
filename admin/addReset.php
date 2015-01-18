<?php 
require_once '../include.php';
checklogined();

$cid=$_GET["cid"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<h3>添加预置位</h3>
<form action="doAdminAction.php?act=addReset&cid=<?php echo $cid;?>" method="post">
<table width="70%" border="1" cellpadding="5" cellspacing="0" bgcolor="#cccccc">
	<tr>
		<td align="right">开始时间</td>
		<td><input type="text" name="fromtime" placeholder="请输入开始时间"/></td>
	</tr>
	<tr>
		<td align="right">结束时间</td>
		<td><input type="text" name="endtime" placeholder="请输入结束时间"/></td>
	</tr>
	<tr>
		<td align="right">俯仰角</td>
		<td><input type="text" name="tilt" placeholder="请输入俯仰角"/></td>
	</tr>
	<tr>
		<td align="right">方位角</td>
		<td><input type="text" name="pan" placeholder="请输入方位角"/></td>
	</tr>
	<tr>
		<td align="right">焦距</td>
		<td><input type="text" name="focal" placeholder="请输入焦距"/></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit"  value="添加预置位"/></td>
	</tr>

</table>
</form>
</body>
</html>