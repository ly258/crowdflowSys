<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<center>
	<h3>系统信息</h3>
	<table width="70%" border="1" cellpadding="5" cellspacing="0" bgcolor="#cccccc">
		<tr>
			<th>操作系统</th>
			<td><?php echo PHP_OS;?></td>
		</tr>
		<tr>
			<th>Apache版本</th>
			<td><?php echo apache_get_version();?></td>
		</tr>
		<tr>
			<th>PHP版本</th>
			<td><?php echo PHP_VERSION;?></td>
		</tr>
		<tr>
			<th>运行方式</th>
			<td><?php echo PHP_SAPI;?></td>
		</tr>
	</table>
	<h3>软件信息</h3>
	<table width="70%" border="1" cellpadding="5" cellspacing="0" bgcolor="#cccccc">
		<tr>
			<th>系统名称</th>
			<td>视频GIS系统</td>
		</tr>
		<tr>
			<th>开发团队</th>
			<td>虚拟地理环境教育部重点实验室&视频GIS研究组</td>
		</tr>
		<tr>
			<th>实验室网址</th>
			<td><a href="http://vgekl.njnu.edu.cn/">http://vgekl.njnu.edu.cn/</a></td>
		</tr>
		<tr>
			<th>成功案例</th>
			<td>常州市公安局</td>
		</tr>
	</table>
</center>

</body>
</html>