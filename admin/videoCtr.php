<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
	<link rel="stylesheet" href="styles/addCamera.css">
	<style type="text/css">
	div#map{
	   width:800px;
	   height:600px;
	}
	label.text{
		color:red;
		font-size:8px;
	}
	#stateCtr{
		width:300px;
		height:40px;
	}
	
	#stateBar{
		width:100%;
		height:20px;
		background-color:#FFFFEE;
	}
	
	#stateText{
		width:100%;
		height:20px;
	}
	</style>
	<script type="text/javascript" src="./scripts/OpenLayers.debug.js"></script>
	<script type="text/javascript" src ="./scripts/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src ="../scripts/MapConfig.js"></script>
	<script type="text/javascript" src ="../scripts/camera.js"></script>
	<script type="text/javascript" src ="../scripts/video.js"></script>
	<script type="text/javascript" src="./scripts/uploadplugin/plupload.full.min.js"></script>
	<script type="text/javascript">
	   var map;
	   var videoCtr;
	   
		//初始化
       function init()
       {
       	 map = MapConfig.createMap("map");
       	 videoCtr=new OpenLayers.VideoEditorCtr(map,$('#videoCore').get(0));
       	 checkEdit(<?php if(!empty($_GET["id"])) echo $_GET["id"]; ?>);
		 videoEventInit();
		 uploadInit();
		 //拖动视频
		 var Drag = new OpenLayers.RDrag($('#videoPlayer').get(0));
		 //	
		 	 		 
       }
	   //控件上传
	   function uploadInit(){	
		   var uploader = new plupload.Uploader({
				runtimes : 'html5,flash,silverlight,html4',
				browse_button : 'pickfiles', // you can pass in id...
				container: document.getElementById('container'), // ... or DOM Element itself
				url : 'upload.php',
				unique_names:true,
				flash_swf_url : './scripts/uploadplugin/Moxie.swf',
				silverlight_xap_url : './scripts/uploadplugin/Moxie.xap',
				
				filters : {
					max_file_size : '128mb',
					mime_types: [
						{title : "video files", extensions : "avi,mkv,mp4,mbf"}
					]
				},

				init: {
					PostInit: function() {
						document.getElementById('filelist').innerHTML = '';

						document.getElementById('uploadfiles').onclick = function() {							var last = uploader.files.length-1;
							uploader.splice(0,uploader.files.length-1);
							uploader.start();
							return false;
						};
					},

					FilesAdded: function(up, files) {
						//plupload.each(files, function(file) {
						file = files[files.length-1];
						document.getElementById('filelist').innerHTML = '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
						//});
						
					},

					UploadProgress: function(up, file) {
						document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
					},
					 FileUploaded: function(up, file, info) {
					 	var rs = eval("("+info.response+")");
                		$("#file").val(rs.path);
                		$("#videoCore").attr("src", rs.path);
           			},
					Error: function(up, err) {
						document.getElementById('console').innerHTML += "\n上传错误 #" + err.code + ": " + err.message;
					}
				}
			});

			uploader.init();
	   }
	   
	   //判断是否是编辑状态
	   function checkEdit(id){
	   	if(!id){
	   		videoRefresh();
	   		$("#startTime").val(new Date().format("yyyy-MM-dd hh:mm:ss"));
		  	$("#endTime").val(new Date().format("yyyy-MM-dd hh:mm:ss"));
	   		return;
	   	} 
	   	    
	   	 $.ajax({
	   	 	type:'GET',
			url:"getEditVideo.php",
			data:{"id":id},
			dataType:'json',
			success:function(data){					
				var videos = OpenLayers.Video.Prase(data);
				//将时间变为相对时间
				for(var i=0;i < videos[0].states.length;i++){
					videos[0].states[i].time = videos[0].states[i].time-videos[0].startTime;
				}
				videoCtr.camera = videos[0];
				videoCtr.CameraCollection.setCameras([videos[0]]);
				$("#name").val(videoCtr.camera.name);
				$("#relate").val(videoCtr.camera.relate);
				$("#file").val(videoCtr.camera.vurl);
				$("#startTime").val(new Date(videoCtr.camera.startTime*1000).format("yyyy-MM-dd hh:mm:ss"));
		  		$("#endTime").val(new Date(videoCtr.camera.endTime*1000).format("yyyy-MM-dd hh:mm:ss"));
		  		$("#videoCore").attr("src",videoCtr.camera.vurl);
				videoCtr.update();
				videoRefresh();
				videoStateCtr(videoCtr.camera.states);
			},
			error:function(){
				alert("请求异常！");
			}
	   	 });
	   	 
	   	 $("#id").val(id);
	   	 $("#submit").val("修改视频");
	   	 $("form").attr("action","doVideoAction.php?action=updateVideo");
	   }
	   //初始化视频状态
	   function videoRefresh(){
			$("#vs_list").empty();
			for(var i = 0;i < videoCtr.camera.states.length;i++){
				$("#vs_list").append("<option value='"+i+"'>"+i+"</option>");
			}
			videoStateCtr(videoCtr.camera.states);			
			select(0);
	   }
	   
	   function timeChange(){
			var s = $("#startTime").val();
			var dt = 0;
			dt = Date.parse(s.replace(/-/g,"/"));
			if(isNaN(dt))
				return;					
	
			videoCtr.camera.startTime = new Date(dt);
			videoCtr.camera.endTime = new Date(dt+$("#videoCore").get(0).duration*1000);
			$("#endTime").val(videoCtr.camera.endTime.format("yyyy-MM-dd hh:mm:ss"));	
	   }
	   //选择
	   function select(id){
	   	  $("#x").attr("value",videoCtr.camera.states[id].x);
		  $("#y").attr("value",videoCtr.camera.states[id].y);
		  $("#z").attr("value",videoCtr.camera.states[id].z);
		  $("#ccd_width").attr("value",videoCtr.camera.states[id].ccdwidth);
		  $("#ccd_height").attr("value",videoCtr.camera.states[id].ccdheight);
		  $("#focal").attr("value",videoCtr.camera.states[id].focal);
		  $("#pan").attr("value",videoCtr.camera.states[id].pan);
		  $("#tilt").attr("value",videoCtr.camera.states[id].tilt);
		  $("#maxdist").attr("value",videoCtr.camera.states[id].maxdist);
		  $("#time").attr("value",videoCtr.camera.states[id].time);
		  
		  $(".cs").css("background-color","blue");
		  $("#cs"+id).css("background-color","red");
		  
		  $(".lb").css("color","black");
		  $("#lb"+id).css("color","red");
		  videoCtr.selectId = id;
		  videoCtr.update();
	   }
	   //初始化事件
	   function videoEventInit(){
			$("#x").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#x").attr("value");
				if(re.test(val)){
					state.x = Number(val);
					videoCtr.update();
					toSphere();
				}
			});
			
			$("#y").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#y").attr("value");
				if(re.test(val)){
					state.y = Number(val);
					videoCtr.update();
					toSphere();
				}
			});
			
			$("#longi").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#longi").attr("value");
				if(re.test(val)){
					toMecator();
					state.x = Number($("#x").val());
					videoCtr.update();					
				}
			});
			
			$("#lat").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#lat").attr("value");
				if(re.test(val)){
					toMecator();
					state.y = Number($("#y").val());
					videoCtr.update();					
				}
			});
			
			$("#z").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#z").attr("value");
				if(re.test(val)){
					state.z = Number(val);
					videoCtr.update();
				}
			});
			
			$("#pan").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#pan").attr("value");
				if(re.test(val)){
					state.pan = Number(val);
					videoCtr.update();
				}
			});
			
			$("#tilt").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#tilt").attr("value");
				if(re.test(val)){
					state.tilt = Number(val);
					videoCtr.update();
				}
			});
			
			$("#focal").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#focal").attr("value");
				if(re.test(val)){
				  state.focal = Number(val);
				  videoCtr.update();
				}
			});
			
			$("#ccd_width").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#ccd_width").attr("value");
				if(re.test(val)){
					for(var i = 0;i < videoCtr.camera.states.length;i++){
						videoCtr.camera.states[i].ccdwidth = Number(val);
				   }
					videoCtr.update();
				}
			});
			
			$("#ccd_height").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#ccd_height").attr("value");
				if(re.test(val)){
					for(var i = 0;i < videoCtr.camera.states.length;i++){
						videoCtr.camera.states[i].ccdheight = Number(val);
				   }
					videoCtr.update();
				}
			});
			
			//状态时间点
			$("#maxdist").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#maxdist").attr("value");
				if(re.test(val)){
					state.maxdist= Number(val);
					videoCtr.update();
				}
			});
			
			//状态时间点
			$("#time").keyup(function(){
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
					
				var state = videoCtr.camera.states[id];
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#time").attr("value");
				if(re.test(val)){
					var maxTime = Math.round($("#videoCore").get(0).duration);
					val = Number(val);
					if(val > maxTime)
					     return;
					     
					state.time = Number(val);
					videoStateCtr(videoCtr.camera.states);
					videoCtr.update();
				}
			});
			
			//选择改变事件
			$("#vs_list").click(function() {	
				var id = $("#vs_list option:selected").val();
				if(id == undefined)
					return;
				//checkTime();
				select(id);
			 });
			
			//注册地图事件
			map.events.register("click",map,function(e){
				 var id = $("#vs_list option:selected").val();
				 if(id == undefined)
					return;
				 var lonlat = map.getLonLatFromViewPortPx(e.xy);
				 var point = MapConfig.rTransform(new OpenLayers.Geometry.Point(
			           lonlat.lon,lonlat.lat));
				 videoCtr.camera.states[id].x = point.x;
				 videoCtr.camera.states[id].y = point.y;
				 videoCtr.update();
				 $("#x").attr("value",videoCtr.camera.states[id].x);
				 $("#y").attr("value",videoCtr.camera.states[id].y);
				 toSphere();
			});
			
			$("#startTime").keyup(function(){
				timeChange();
			});			
			$("#videoCore").bind("loadedmetadata",function(){
				timeChange();
			});
			$("#endTime").attr("readOnly",true);
			//检查时间正确性
			
			$("#videoCore").bind("play",function(){	   	
				clearInterval(playfunc.bind(this));
				setInterval(playfunc.bind(this),40);				
			});
			
			$("#videoCore").bind("pause",function(){	   	
				clearInterval(playfunc.bind(this));				
			});
			
			$("#videoCore").bind("abort",function(){	   	
				clearInterval(playfunc.bind(this));				
			});
			
			toSphere();
			//checkTime();
			//var timeString = new Date().format("yyyy-MM-dd hh:mm:ss");
		  	//$("#startTime").attr("value",timeString);
		  	//$("#endTime").attr("value",timeString);
	   }
	   //添加视频状态
	   function addState(){
			var camState = new OpenLayers.Camera.State();
			camState.x = $("#x").val();
			camState.y = $("#y").val();
			camState.z = $("#z").val();
			camState.pan = $("#pan").val();
			camState.tilt = $("#tilt").val();
			camState.focal = $("#focal").val();
			camState.ccdwidth = $("#ccd_width").val();
			camState.ccdheight = $("#ccd_height").val();
			camState.time = $("#time").val();
			videoCtr.addCameraState(camState);
			videoRefresh();
	   }
	   //移除状态
	   function removeState(){
			videoCtr.removeCameraState($("#vs_list option:selected").val());
			videoRefresh();
	   }
	   //预提交讲state转换为Json
	   function preSubmit(){
			var json ="[";
			for(var i = 0;i < videoCtr.camera.states.length;i++ ){
				if(i != 0)
				    json += ",";
					
				var stateJson ="{";
				stateJson +="\"x\":\""+videoCtr.camera.states[i].x+"\",";
				stateJson +="\"y\":\""+videoCtr.camera.states[i].y+"\",";
				stateJson +="\"z\":\""+videoCtr.camera.states[i].z+"\",";
				stateJson +="\"focal\":\""+videoCtr.camera.states[i].focal+"\",";
				stateJson +="\"pan\":\""+videoCtr.camera.states[i].pan+"\",";
				stateJson +="\"tilt\":\""+videoCtr.camera.states[i].tilt+"\",";
				stateJson +="\"maxdist\":\""+videoCtr.camera.states[i].maxdist+"\",";
				stateJson +="\"time\":\""+videoCtr.camera.states[i].time+"\"";
				stateJson +="}";
				json += stateJson;	
			}
			json += "]";
			$("#statesJson").attr("value",json);
			//alert(json);
	   }
	   
	   function showVideo(){
	   	  $("#videoPlayer").show();
	   	  $("#videoCore").get(0).play();
	   }
	   
	/****************************
 	*视频状态维护
 	* 
 	****************/
	   function videoStateCtr(){
	   	   var maxTime = Math.round($("#videoCore").get(0).duration);
	   	   
	   	   if(isNaN(maxTime)){
	   	   		setTimeout("videoStateCtr()",100);
	   	   		return;	
	   	   }
	   	   
	   	   var stateBar = $("#stateBar");
	   	   var stateText = $("#stateText");
	   	   stateBar.empty();
	   	   stateText.empty();
	   	   //$("#videoCore").bind("loadedmetadata",function(){
	   	    var	cameraStates = videoCtr.camera.states;
	   	   	var marker = $("<lable></label>");
		   	marker.css("margin-left","300px");
			marker.text(maxTime);
			marker.css("position","absolute");
	   	   	marker.appendTo(stateText);
	   	   	var barWidth = stateBar.width();
	   	   	for(var i=0;i < cameraStates.length;i++){
		   	   	var marker = $("<div></div>");
		   	   	var pos = cameraStates[i].time/maxTime*barWidth;
		   	   	marker.attr("id","cs"+i);
		   	   	marker.attr("class","cs");
		   	   	marker.css("width","10px");
		   	   	marker.css("height","20px");
		   	   	marker.css("background-color","blue");
		   	   	marker.css("margin-left",pos+"px");
		   	   	marker.css("position","absolute");
		   	   	marker.appendTo(stateBar);
		   	   		
		   	   	var lable = $("<lable></label>");
		   	   	lable.attr("id","lb"+i);
		   	   	lable.attr("class","lb");
			   	lable.css("margin-left",pos+"px");
				lable.text(cameraStates[i].time);
				lable.css("position","absolute");
		   	   	lable.appendTo(stateText);
	   	   	}
	   //});	   
	  }
	  
	  
	  function playfunc(){
			var stateBar = $("#stateBar");
			var stateText = $("#stateText");
			var barWidth = stateBar.width();
			var pos = this.currentTime/this.duration*barWidth;
				   	
			if(!videoCtr.timeMarker
			&&!videoCtr.timeLable){
				  var marker = $("<div></div>");
				  marker.attr("id","csidx");
				  marker.attr("class","csidx");
				  marker.css("width","10px");
				  marker.css("height","20px");
				  marker.css("background-color","#00FF00");
				  marker.css("position","absolute");
				  marker.appendTo(stateBar);
				  videoCtr.timeMarker = marker;
				  var lable = $("<lable></label>");
				  lable.attr("ididx","lbidx");
				  lable.attr("class","lbidx");
				  lable.text(this.currentTime);
				  lable.css("position","absolute");
				  lable.css("color","#00FF00");
				  lable.appendTo(stateText);
				  videoCtr.timeLable = lable;
		  };
		  
	      videoCtr.timeMarker.css("margin-left",pos+"px");
		  videoCtr.timeLable.css("margin-left",pos+"px");
		  videoCtr.timeLable.text(this.currentTime);	   	
		}
		
		function  projSwitch() {
		  if($("#pswitch").val()=="显示经纬度"){
		  	$("#pswitch").val("显示投影");
		  	$("#longi").show();
		  	$("#lat").show();
		  	$("#x").hide();
		  	$("#y").hide();		  		  	
		  }else{
		  	$("#pswitch").val("显示经纬度");
		  	$("#longi").hide();
		  	$("#lat").hide();
		  	$("#x").show();
		  	$("#y").show();	
		  }
		}
		
		function toMecator(){
			var myLocation = new OpenLayers.Geometry.Point($("#longi").val(), $("#lat").val())
        					.transform('EPSG:4326', 'EPSG:900913');
        	$("#x").val(myLocation.x);
        	$("#y").val(myLocation.y);
		}
		
		function toSphere(){
			var myLocation = new OpenLayers.Geometry.Point($("#x").val(), $("#y").val())
        					.transform('EPSG:900913', 'EPSG:4326');
        	$("#longi").val(myLocation.x);
        	$("#lat").val(myLocation.y);
		}
</script>
</head>
<body onload="init()">
  <table width ="100%" height="100%">
	<tr>
	  <td width="800">
		<div id="map">
		</div>
	  </td>
	  <td align="left" valign="top">
	  <form action="doVideoAction.php?action=addVideo" method="post" enctype="multipart/form-data">
	    <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#cccccc">
		   <tr>
				<td align="right" width=70>视频名称</td>
				<td>
					<input id="name" type="text" name="name" placeholder="请输入摄像机名称"/>
					<input id="id" type="hidden" name="id"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>视频url</td>
				<td>
					<!--<input type="file" name="videofile" />-->
					<div id="filelist" style="height:20px;">
					     您的浏览器不支持Flash，silverlight或html5
					</div>
					<div id="container">
						<button id="pickfiles" href="javascript:;">选择文件</button> 
						<button id="uploadfiles" href="javascript:;">上传文件</button>
					</div>
					<pre id="console"></pre>
					<input id = "file" name = "file" type="hidden"  value=""/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>关联像机</td>
				<td>
					<input id="relate" type="text" name="relate" placeholder="请输入关联摄像机"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>宽度</td>
				<td>
					<input id="ccd_width" type="text" name="ccd_width" placeholder="视频宽度"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>高度</td>
				<td>
					<input id="ccd_height" type="text" name="ccd_height" placeholder="视频高度"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>时间：</td>
				<td>
					<input id="startTime" type="text" name="startTime" placeholder="起始时间"/>
					- <input id="endTime" type="text" name="endTime" placeholder="终止时间"/>
				</td>
			</tr>
			<tr>
			<td height="30">
			    状态条：	
			</td>
			<td>
			  <div id="stateCtr">
			   <div id="stateBar"></div>
			   <div id="stateText"></div>
			  <div>
			</td>
			</tr>					
		   	<tr>
		   	
			<td colspan="2">
					<b>摄像机状态</b>
			</td>
		   </tr>
		   <tr>
		   <td colspan="2">
			    <table>
					<tr>
						<td align="right">x</td>
						<td>
							<input id="x" type="text" name="x" placeholder="x坐标"/>
							<input id="longi" type="text" placeholder="x坐标" style="display: none"/>
						</td>
						<td rowspan="9">
						   <select id="vs_list" name="vs_list" multiple="true"  size="15"  style="width:100px">   
						   </select>
						</td>
				   </tr>
				   <tr>
						<td align="right">y</td>
						<td>
							<input id="y" type="text" placeholder="y坐标"/>
							<input id="lat" type="text" placeholder="y坐标" style="display: none"/>
						</td>
				   </tr>
				   <tr>
						<td align="right">z</td>
						<td><input id="z" type="text" name="focal" placeholder="z坐标"/></td>
				   </tr>
		           <tr>
						<td align="right">焦距</td>
						<td><input id="focal" type="text" name="focal" placeholder="焦距"/></td>
				   </tr>
				   <tr>
						<td align="right">方位角</td>
						<td><input id="pan" type="text" name="pan" placeholder="方位角"/></td>
				   </tr>
				    <tr>
						<td align="right">俯仰角</td>
						<td><input id="tilt" type="text" name="tilt" placeholder="俯仰角"/></td>
				   </tr>
				   <tr>
						<td align="right">最大可视距离</td>
						<td>
							<input id="maxdist" type="text" name="maxdist" placeholder="最大可视距离" value="300"/>
						</td>
					</tr>
                    <tr>
						  <td align="right">时间点</td>
						  <td>
					         <input id="time" type="text" name="time" placeholder="时间点"/>
				          </td>
		            </tr>
					<tr>
						<td colspan=2>
							<input type="button"  value="添加状态" onclick="addState()"/>
							<input type="button"  value="删除状态" onclick="removeState()"/>
							<input id="pswitch" type="button"  value="显示经纬度" onclick="projSwitch()"/>
							<input id="statesJson" type="hidden" name="statesJson" />
						</td>
		            </tr>
			    </table>
		   </td>
		   </tr>
		   <tr>
			    <td colspan="2">
					<input id = "submit" type="submit"  value="添加视频" onclick="preSubmit()"/><input type="button"  value="视频预览" onclick="showVideo()"/>
				</td>
		   </tr>
		</table>
	  </form>
	  </td>
	</tr>
  </table>
  <div id = "videoPlayer" style="background-color:#4b6c9e;width:480px;height:320px;border:#ccc solid 1px;z-index:9999;
			position:absolute;border-radius:15px;left: 200px;top:200px;display:none">
			<div id = "videoTitle" style="background-color:#4b6c9e; width:480px;height:25px;border-radius:15px 15px 0px 0px;">
    <img  alt = "找不到图片" width = "24" height = "24" src = "images/error.png"  style="margin-left:450px;cursor:pointer" onclick = "$('#videoPlayer').hide();$('#videoCore').get(0).pause();"/>
			</div>
			   <video id="videoCore"  style="width:480px" controls="controls">
    			<source src="../mov_bbb.mp4" type="video/mp4" />
    			Your browser does not support HTML5 video.
  		       </video>
  </div>
</body>