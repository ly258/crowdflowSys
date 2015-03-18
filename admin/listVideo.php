<?php
   require_once '../include.php';
   //checklogined();
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
  <title>视频列表</title>
  <link href="styles/listVideo.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src = "../scripts/OpenLayers.debug.js"></script>
  <script type="text/javascript" src ="./scripts/jquery-1.11.2.min.js"></script>
  <script type="text/javascript" src ="../scripts/MapConfig.js"></script>
  <script type="text/javascript" src ="../scripts/camera.js"></script>
  <script type="text/javascript" src="../scripts/video.js"></script>
  <script type="text/javascript" src="../scripts/DemoVideoJson.js"></script>
  <script type="text/javascript">
      var videoViewer;
      function init(){
        map = MapConfig.createMap("map");       
        
        
		var videoCollection = new OpenLayers.VideoCollection([]
					                  ,$('#videoCore').get(0));
				
		var Drag = new OpenLayers.RDrag($('#videoPlayer').get(0));
		var videoList = new OpenLayers.VideoList("rsList");
		videoList.defaultStyle=function(i)
        {
          var videos = this.cameras;
	
          var style= $("<div id="+i+" class=\"item\" style=\"width:100%;height:100px;cursor:pointer\">"
				+"<table width=\"100%\" height=\"100%\">"
				+"	<tr><td  width=\"40\" align=\"center\" align=\"center\" valign=\"center\">"+(i+1)+"</td>"
				+"     <td>"
				+"		<table width=\"100%\" height=\"100%\" style=\"font-size:12px\">"
				+"			  <tr>"
				+"				  <td width=\"70\">名称:</td>"
				+"				  <td>"+videos[i].name+"</td>"
				+"			  </tr>"
				+"			  <tr>"
				+"				  <td>关联摄像机:</td>"
				+"				  <td>"+((videos[i].relateCamera==-1) ? "无" : videos[i].relateCamera)+"</td>"
				+"			  </tr>"
				+"			  <tr>"
				+"				  <td>起始时间:</td>"
				+"				  <td>"+new Date(videos[i].startTime*1000).format("yyyy-MM-dd hh:mm:ss")+"</td>"
				+"			  </tr>"
				+"			  <tr>"
				+"				  <td>结束时间：</td>"
				+"				  <td>"+new Date(videos[i].endTime*1000).format("yyyy-MM-dd hh:mm:ss")+"</td>"
				+"			  </tr>"
				+"			  <tr>"
				+"				  <td colspan='2'>"
				+"                <input type='button' value='编辑' onclick='editCamera("+videos[i].id+")'/>     "
				+"                <input type='button' value='删除' onclick='delCamera("+videos[i].id+")'/>     "
				+"</td>"
				+"			  </tr>"
				+"			</table>"
				+"		</td>"					
				+"	</tr>"
				+"</table>"
			+"</div>");
          return style;
        };
		var videoQueryer = new OpenLayers.VideoQueryer();
		videoQueryer.queryurl = "../videoQuery.php";
		videoViewer = new OpenLayers.VideoViewer(videoCollection,videoList,videoQueryer);
		videoViewer.attach(map);
		videoQueryer.query("","","");
		$("#startTime").val(new Date().format("yyyy-MM-dd hh:mm:ss"));
		$("#endTime").val(new Date().format("yyyy-MM-dd hh:mm:ss"));
		timeCheck();
		spatialCheck();
      }
      
      function modeChange(element){
      	videoViewer.cameraQueryer.setQueryMode(element.value);
      }
      
      function search(){
      	var startTime="";
      	var endTime="";
      	if($("#checkTime").attr("checked")){
      		startTime = $("#startTime").val();
      		endTime = $("#endTime").val();
      	}
      	videoViewer.cameraQueryer.query($("#searchTxt").val()
      	                                ,startTime,endTime);
      }
      
      function timeCheck(){
      	if($("#checkTime").attr("checked")){
      		$("#startTime").attr("disabled",false);
      		$("#endTime").attr("disabled",false);
      	}else{
      		$("#startTime").attr("disabled",true);
      		$("#endTime").attr("disabled",true);
      	}
      }
      
      function spatialCheck(){
      	if($("#checkSpatial").attr("checked")){
      		$("input[name='georadio']").each(function(){    			
      			this.disabled = false;
      			if(this.checked){
      				videoViewer.cameraQueryer.setQueryMode(this.value);
      			}
      				
      		});
      	}else{
      		$("input[name='georadio']").attr("disabled",true);
      		videoViewer.cameraQueryer.setQueryMode("none");
      	}
      }
      
     function delCamera(id)
     {
       if(confirm("您确定要删除吗？"))
       {
           window.location="doVideoAction.php?action=delVideo&id="+id;
       }    
     }
     
     function editCamera(id)
     {
        window.location="videoCtr.php?id="+id;
     }
</script>
</head>
<body onload="init()">
  <div id="searth" class="searchContainer">
  	<div class="searchSub" style="width:600px;padding-top:10px">
    <input id ="searchTxt" type="text"/>
    <input id="searchBtn"  type="button" style="height:30px" value="搜 索" onclick="search()"/>
    </div>
    <div class = "searchSub" style="width:550px">    
    <table>
    	<tr>
    		<td><b>时间查询：</b></td>
    		<td>
    			<input id ="startTime" type="text" value="不限"/>
    			=>
    			<input id ="endTime" type="text" value="不限"/>
    		</td>
    		<td>
    	                  开启<input id ="checkTime" type="checkbox" onclick="timeCheck()"/>
     	    </td>
    	</tr>
    		<td><b>空间查询：</b></td>
    		<td>	   
	    	 	<input id="point" name = "georadio" type="RADIO" value="point" checked="checked" onclick="modeChange(this);"/>点&nbsp;
		    	<input id="line" name = "georadio" type="RADIO" value="line" onclick="modeChange(this);"/>线&nbsp;
		    	<input id="rect" name = "georadio" type="RADIO" value="rect" onclick="modeChange(this);"/>范围&nbsp;
		    	<input id="polygon" name = "georadio" type="RADIO" value="polygon" onclick="modeChange(this);"/>多边形&nbsp;
			</td>
			<td>
	    		开启<input id ="checkSpatial" type="checkbox" onclick="spatialCheck()"/>
	    	</td>
    </table>
    </div>
  </div>
  <table>
  <div class="mainContainer">
    <div id="rsList" class="tocContainer"></div>
    <div class="mapContainer">
    	<div id="map">
    		
    	</div>
    </div>
  </div>
  <div id = "videoPlayer">
			<div id = "videoTitle">
    <img  alt = "找不到图片" width = "24" height = "24" src = "images/error.png"  style="margin-left:450px;cursor:pointer" onclick = "$('#videoPlayer').hide()"/>
			</div>
			   <video id="videoCore"  style="width:480px" controls="controls">
    			<source src="../mov_bbb.mp4" type="video/mp4" />
    			Your browser does not support HTML5 video.
  		       </video>
  </div>
  </table>
  <!--
  <div id="error" style="margin-top: 300px">
  	
  </div>
  -->
</body>
</html>