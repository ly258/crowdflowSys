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
	</style>
	<script type="text/javascript" src="./scripts/OpenLayers.debug.js"></script>
	<script type="text/javascript" src ="./scripts/jquery-1.6.4.js"></script>
	<script type="text/javascript" src ="../scripts/MapConfig.js"></script>
	<script type="text/javascript" src ="../scripts/camera.js"></script>
	<script type="text/javascript" src="./scripts/uploadplugin/plupload.full.min.js"></script>
	<script type="text/javascript">
	   var map;
	   var videoCtr;
	   //日期解析
	   Date.prototype.format =function(format){
		var o = {
			"M+" : this.getMonth()+1, //month
			"d+" : this.getDate(), //day
			"h+" : this.getHours(), //hour
			"m+" : this.getMinutes(), //minute
			"s+" : this.getSeconds(), //second
			"q+" : Math.floor((this.getMonth()+3)/3), //quarter
			"S" : this.getMilliseconds() //millisecond
		}
		if(/(y+)/.test(format)) format=format.replace(RegExp.$1,
		(this.getFullYear()+"").substr(4- RegExp.$1.length));
		for(var k in o)if(new RegExp("("+ k +")").test(format))
		format = format.replace(RegExp.$1,
		RegExp.$1.length==1? o[k] :
		("00"+ o[k]).substr((""+ o[k]).length));
		return format;
		}
		//初始化
       function init()
       {
       	 map = MapConfig.createMap("map");
       	 videoCtr=new OpenLayers.VideoEditorCtr(map);
		 videoRefresh();
		 videoEventInit();
		 uploadInit();
		 //		 		 
       }
	   //控件上传
	   function uploadInit(){	
		   var uploader = new plupload.Uploader({
				runtimes : 'html5,flash,silverlight,html4',
				browse_button : 'pickfiles', // you can pass in id...
				container: document.getElementById('container'), // ... or DOM Element itself
				url : 'upload.php',
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

						document.getElementById('uploadfiles').onclick = function() {
							uploader.start();
							return false;
						};
					},

					FilesAdded: function(up, files) {
						plupload.each(files, function(file) {
							document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
						});
					},

					UploadProgress: function(up, file) {
						document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
					},

					Error: function(up, err) {
						document.getElementById('console').innerHTML += "\n上传错误 #" + err.code + ": " + err.message;
					}
				}
			});

			uploader.init();
	   }
	   //初始化视频状态
	   function videoRefresh(){
			$("#vs_list").empty();
			for(var i = 0;i < videoCtr.camera.states.length;i++){
				$("#vs_list").append("<option value='"+i+"'>"+i+"</option>");
			}			
			$("#x").attr("value",videoCtr.camera.states[0].x);
			$("#y").attr("value",videoCtr.camera.states[0].y);
			$("#z").attr("value",videoCtr.camera.states[0].z);
			$("#ccd_width").attr("value",videoCtr.camera.states[0].ccdwidth);
			$("#ccd_height").attr("value",videoCtr.camera.states[0].ccdheight);
			$("#focal").attr("value",videoCtr.camera.states[0].focal);
			$("#pan").attr("value",videoCtr.camera.states[0].pan);
			$("#tilt").attr("value",videoCtr.camera.states[0].tilt);
			$("#maxdist").attr("value",videoCtr.camera.states[0].maxdist);
			var timeString = new Date().format("yyyy-MM-dd hh:mm:ss");
			$("#startTime").attr("value",timeString);
			$("#endTime").attr("value",timeString);
			$("#vs_startTime").attr("value",timeString);
			$("#vs_endTime").attr("value",timeString);
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
			//注册地图事件
			map.events.register("click",map,function(e){
				 var id = $("#vs_list option:selected").val();
				 if(id == undefined)
					return;
				 var lonlat = map.getLonLatFromViewPortPx(e.xy);
				 videoCtr.camera.states[id].x = lonlat.lon;
				 videoCtr.camera.states[id].y = lonlat.lat;
				 videoCtr.update();
				 $("#x").attr("value",videoCtr.camera.states[id].x);
				 $("#y").attr("value",videoCtr.camera.states[id].y);
			});
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
				stateJson +="\"x\":\""+$("#x").val()+"\",";
				stateJson +="\"y\":\""+$("#y").val()+"\",";
				stateJson +="\"z\":\""+$("#z").val()+"\",";
				stateJson +="\"focal\":\""+$("#focal").val()+"\",";
				stateJson +="\"pan\":\""+$("#pan").val()+"\",";
				stateJson +="\"tilt\":\""+$("#tilt").val()+"\",";
				stateJson +="\"maxdist\":\""+$("#maxdist").val()+"\",";
				stateJson +="\"startTime\":\""+$("#startTime").val()+"\",";
				stateJson +="\"endTime\":\""+$("#endTime").val()+"\"";
				stateJson +="}";
				json += stateJson;	
			}
			json += "]";
			$("#statesJson").attr("value",json);
			//alert(json);
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
	  <form action="doVideoAction.php" method="post" enctype="multipart/form-data">
	    <table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#cccccc">
		   <tr>
				<td align="right" width=70>视频名称</td>
				<td>
					<input type="text" name="name" placeholder="请输入摄像机名称"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>视频url</td>
				<td>
					<!--<input type="file" name="videofile" />-->
					<div id="filelist">
					     您的浏览器不支持Flash，silverlight或html5
					</div>
					<div id="container">
						<a id="pickfiles" href="javascript:;">[选择文件]</a> 
						<a id="uploadfiles" href="javascript:;">[上传文件]</a>
					</div>
					<br />
					<pre id="console"></pre>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>关联像机</td>
				<td>
					<input type="text" name="relate" placeholder="请输入关联摄像机"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>起始时间</td>
				<td>
					<input id="startTime" type="text" name="startTime" placeholder="起始时间"/>
				</td>
		   </tr>
		   <tr>
				<td align="right" width=70>终止时间</td>
				<td>
					<input id="endTime" type="text" name="endTime" placeholder="终止时间"/>
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
				<td colspan="2">
					<b>摄像机状态</b>
				</td>
		   </tr>
		   <tr>
		   <td colspan="2">
			    <table>
					<tr>
						<td align="right">x</td>
						<td><input id="x" type="text" name="focal" placeholder="x坐标"/></td>
						<td rowspan="9">
						   <select id="vs_list" name="vs_list" multiple="true"  size="15"  style="width:100px">   
						   </select>
						</td>
				   </tr>
				   <tr>
						<td align="right">y</td>
						<td><input id="y" type="text" name="focal" placeholder="y坐标"/></td>
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
						<td align="right">起始时间</td>
						<td>
							<input id="vs_startTime" type="text" name="vs_startTime" placeholder="起始时间"/>
						</td>
					</tr>
                    <tr>
						  <td align="right">终止时间</td>
						  <td>
					         <input id="vs_endTime" type="text" name="vs_endTime" placeholder="终止时间"/>
				          </td>
		            </tr>
					<tr>
						<td colspan=2>
							<input type="button"  value="添加状态" onclick="addState()"/>
							<input type="button"  value="删除状态" onclick="removeState()"/>
							<input type="button"  value="清空状态" onclick="removeState()"/>
							<input id="statesJson" type="hidden" name="statesJson" />
						</td>
		            </tr>
			    </table>
		   </td>
		   </tr>
		   <tr>
			    <td colspan="2">
					<input type="submit"  value="添加视频" onclick="preSubmit()"/>
				</td>
		   </tr>
		</table>
	  </form>
	  </td>
	</tr>
  </table>
</body>