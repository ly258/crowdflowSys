<?php 
require_once '../include.php';
checklogined();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>视频GIS后台管理系统</title>
<link rel="stylesheet" href="styles/backstage.css">
</head>

<body>
    <div class="head">
            <h3 class="head_text fr">视频GIS后台管理系统</h3>
    </div>
    <div class="operation_user clearfix">
       <!--   <div class="link fl"><a href="#">慕课</a><span>&gt;&gt;</span><a href="#">商品管理</a><span>&gt;&gt;</span>商品修改</div>-->
        <div class="link fr">
            <b>欢迎您
            <?php 
				if(isset($_SESSION['adminName'])){
					echo $_SESSION['adminName'];
				}elseif(isset($_COOKIE['adminName'])){
					echo $_COOKIE['adminName'];
				}
            ?>
            
            </b>&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php" class="icon icon_i">首页</a><span></span><a href="javascript:history.go(1);" class="icon icon_j">前进</a><span></span><a href="javascript:history.go(-1);" class="icon icon_t">后退</a><span></span><a href="javascript:history.go(0);" class="icon icon_n">刷新</a><span></span><a href="doAdminAction.php?act=logout" class="icon icon_e">退出</a>
        </div>
    </div>
    <div class="content clearfix">
        <div class="main">
            <!--右侧内容-->
            <div class="cont">
                <div class="title">后台管理</div>
      	 		<!-- 嵌套网页开始 -->         
                <iframe src="main.php"  frameborder="0" name="mainFrame" width="100%" height="640"></iframe>
                <!-- 嵌套网页结束 -->   
            </div>
        </div>
        <!--左侧列表-->
        <div class="menu">
            <div class="cont">
                <div class="title">管理员</div>
                <ul class="mList">
                <li>
                        <h3 onclick="show('menu1','change1')"><span id="change1">+</span>管理员管理</h3>
                        <dl id="menu1" style="display:none;">
                        	<dd><a href="addAdmin.php" target="mainFrame">添加管理员</a></dd>
                            <dd><a href="listAdmin.php?page=1" target="mainFrame">管理员列表</a></dd>
                        </dl>
                    </li>
                    <li>
                        <h3 onclick="show('menu2','change2')"><span id="change2">+</span>视频管理</h3>
                        <dl id="menu2" style="display:none;">
                        	<dd><a href="videoCtr.php" target="mainFrame">添加视频</a></dd>
                            <dd><a href="listVideo.php" target="mainFrame">视频列表</a></dd>
                        </dl>
                    </li>
                    <li>
                        <h3 onclick="show('menu3','change3')"><span id="change3">+</span>摄像机管理</h3>
                        <dl id="menu3" style="display: none;">
                        <dd><a href="addCamera.php" target="mainFrame">添加摄像机</a></dd>
                        <dd><a href="listCamera.php" target="mainFrame">摄像机列表</a></dd>
                        <dd><a href="fov.php" target="mainFrame">覆盖范围计算</a></dd>
                        </dl>
                    </li>
                    <li>
                        <h3 onclick="show('menu4','change4')"><span id="change4">+</span>视频几何量测</h3>
                        <dl id="menu4" style="display: none;">
                        <dd><a href="setMeasureCoor.php" target="mainFrame">坐标系设置</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
        </div>

    </div>
    <script type="text/javascript">
    	function show(num,change){
	    		var menu=document.getElementById(num);
	    		var change=document.getElementById(change);
	    		if(change.innerHTML=="+"){
	    				change.innerHTML="-";
	        	}else{
						change.innerHTML="+";
	            }
    		   if(menu.style.display=='none'){
    	             menu.style.display='';
    		    }else{
    		         menu.style.display='none';
    		    }
        }
    </script>
</body>
</html>