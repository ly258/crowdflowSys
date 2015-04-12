<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
<h3>添加人群计算服务器</h3>
<form action="doServerAction.php?act=addServer" method="post">
<table width="70%" border="1" cellpadding="5" cellspacing="0" bgcolor="#cccccc">
	<tr>
		<td align="right">服务器IP</td>
		<td><input id="serverIp" type="text" name="ip" placeholder="请输入服务器IP地址"/></td>
	</tr>
	<tr>
		<td align="right">服务器端口</td>
		<td><input id="serverPort" type="text" name="port" placeholder="请输入服务器端口" /></td>
	</tr>
	<tr>
		<td colspan="2"><input type="button" onclick=testServer()  value="测试连接"/><input onclick=addServer() id="addServerButton" type="button" disabled=true value="添加连接"/><input onclick=returnServerManager() type="button"  value="返回"/></td>
	</tr>

</table>
</form>
<script src="./scripts/jquery-1.11.2.min.js"></script>
<script>
    function testServer()
    {
        ip = document.getElementById("serverIp").value;
        port = parseInt(document.getElementById("serverPort").value);
    	$.ajax({
            url:"doServerAction.php?act=testServer",
            data:{ip:this.ip,port:this.port},
            type:"post",
            success:function(data)
            {
                if(data == "PONG")
             	   {
                      alert("连接成功");
                      $("#addServerButton").removeAttr("disabled");
             	   }
                else
             	   alert("连接失败");
            }
        });
	}

	function addServer()
	{
		ip = document.getElementById("serverIp").value;
		port = parseInt(document.getElementById("serverPort").value);
		$.ajax({
			url:"doServerAction.php?act=addServer",
			data:{ip:this.ip,port:this.port},
			type:"post",
			success:function(data)
			{
			   	if(data=="addSuccess")
			   	{
			   		alert("添加成功");
				}
			   	else
	             	alert("添加失败");
		    }
		});
	}

	function returnServerManager()
	{
		window.location = "serverManager.php";
	}
</script>
</body>
</html>