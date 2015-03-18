<?php
require_once '../include.php';
checklogined();
$sql="select * from videocms_measurecoor where id='1'";
$row=fetchOne($link, $sql); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Insert title here</title>
	<script type="text/javascript" src ="./scripts/jquery-1.11.2.min.js"></script>
	<script>
		function init(type)
		{
			$("#coorsystem").val(type);
		}

		function check()
		{
			if($("#Xc").val()==""||$("#Yc").val()==""||$("#Hc").val()==""||$("#a").val()=="")
		    {
		    	alert("坐标信息输入不完整");
			    return false;
			}
			if($("#a").val()-360>0||$("#a").val()-0<0)
			{
			    alert("相机光轴方位角应大于0度小于360度，请重新设置摄像机俯仰角范围");
			    return false;	
		    }
		}
	</script>
</head>
<body onload="init(<?php echo $row['isgeographic']; ?>)">
	<h3>视频几何量测坐标系设置</h3>
	<form action="doAdminAction.php?act=setcoorsystem" method="post" onsubmit="return check()">
		<table width="70%" border="1" cellpadding="5" cellspacing="0" bgcolor="#cccccc">
			<tr>
				<td align="right">坐标系选择</td>
				<td>
					<select id="coorsystem" name="isgeographic">
						<option value="0">局部坐标系</option>
						<option value="1">世界坐标系</option>
					</select>
				</td>
			</tr>
			<tr>
				<td align="right">相机中心X坐标</td>
				<td>
					<input type="text" id="Xc" name="xc" placeholder=<?php echo $row['xc'];?>></td>
			</tr>
			<tr>
				<td align="right">相机中心Y坐标</td>
				<td>
					<input type="text" id="Yc" name="yc" placeholder=<?php echo $row['yc'];?>></td>
			</tr>
			<tr>
				<td align="right">相机中心Z坐标</td>
				<td>
					<input type="text" id="Hc" name="hc" placeholder=<?php echo $row['hc'];?>></td>
			</tr>
			<tr>
				<td align="right">相机光轴方位角</td>
				<td>
					<input type="text" id="a" name="a" placeholder=<?php echo $row['a'];?>></td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit"  value="提交"/>
				</td>
			</tr>

		</table>
	</form>
</body>
</html>