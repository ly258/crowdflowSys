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
                         serverAddress = obj.reference.context.innerText;
                         index = serverAddress.indexOf(":");
                         alert(index);
                    }  
                },  
                "停止": {  
                    "label": "停止",  
                    "action": function(obj) { alert(obj) } 
                    } ,
                "重启": {  
                    "label": "重启",  
                    "action": function(obj) { alert(obj) } 
                    },
                "新建服务器": {  
                    "label": "新建服务器",  
                    "action": function(obj) { alert(obj) } 
                    },
                "删除服务器": {  
                    "label": "删除服务器",  
                    "action": function(obj) { alert(obj) } 
                    },
                },
        "select_node" :false, 
            }    
	});
  
  
  </script>
</body>
</html>