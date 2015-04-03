<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>分布式系统服务器管理工具</title>
  <link rel="stylesheet" href="./styles/themes/default/style.min.css" />
  <link rel="stylesheet" href="./styles/serverManager.css" />
</head>
<body>
  <h1>服务器列表</h1>
  <div id="list" class="serverlist"></div>
  <div id="detail" class="serverdetail">
  </div>

  <script src="./scripts/jquery-1.11.2.min.js"></script>
  <script src="./styles/jstree.min.js"></script> 
  <script>
  //ajax demo
	$('#list').jstree({
		'core' : {
			'data' : {
				"url" : "doServerAction.php?act=getServerName",
				"dataType" : "json" // needed only if you do not supply JSON headers
			}
		},
     "plugins" : [ "themes", "html_data", "contextmenu" ],   
        "contextmenu": {  
            "items": {  
                "create": null,  
                "rename": null,  
                "remove": null,  
                "ccp": null,  
                "启动": {  
                    "label": "启动",  
                    "action": function (obj) 
                    {
                         address = this.getServerAddress(obj);
                         $.ajax({
                             url:"doServerAction.php?act=startServer",
                             data:{ip:address[0],port:address[1]},
                             type:"post",
                             success:function(data)
                             {
                                 if(data == "started")
                              	   alert("启动成功！");
                                 else
                              	   alert("启动失败，请重试！");
                             }
                         });
                         //window.location = "doServerAction.php?act=startServer&serverip="+ip+"&port="+port;
                    }  
                },  
                "停止": {  
                    "label": "停止",  
                    "action": function(obj) 
                    {
                    	address = this.getServerAddress(obj);
                        $.ajax({
                            url:"doServerAction.php?act=stopServer",
                            data:{ip:address[0],port:address[1]},
                            type:"post",
                            success:function(data)
                            {
                                if(data == "stoped")
                             	   alert("停止成功！");
                                else
                             	   alert("停止失败，请重试！");
                            }
                        });
                    }
                    } ,
                "重启": {  
                    "label": "重启",  
                    "action": function(obj)
                    {
                    	address = this.getServerAddress(obj);
                        $.ajax({
                            url:"doServerAction.php?act=restartServer",
                            data:{ip:address[0],port:address[1]},
                            type:"post",
                            success:function(data)
                            {
                                if(data == "restarted")
                             	   alert("重启成功");
                                else
                             	   alert("重启失败，请重试！");
                            }
                        });
                    }
                    },
                "新建服务器": {  
                    "label": "新建服务器",  
                    "action": function(obj) 
                    {
                        window.location = "addServer.php";
                    }
                    },
                "删除服务器": {  
                    "label": "删除服务器",  
                    "action": function(obj) { alert(obj) } 
                    },
                },
        "select_node" :false, 
            }    
	});

	function getServerAddress(obj)
	{
		serverAddress = obj.reference.context.innerText;
        index = serverAddress.indexOf(":");
        ip = serverAddress.substring(0,index);
        port = parseInt(serverAddress.substring(index+1));
        address = new Array();
        address.push(ip);
        address.push(port);
        return address;
	}
  
  
  </script>
</body>
</html>