<?php 
require_once '../include.php';
checklogined();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Insert title here</title>
	<link rel="stylesheet" href="styles/addCamera.css">
	<script type="text/javascript" src="./scripts/OpenLayers.debug.js"></script>
	<script type="text/javascript" src ="./scripts/jquery-1.6.4.js"></script>
	<script type="text/javascript" src ="../scripts/MapConfig.js"></script>
	<script type="text/javascript" src ="../scripts/camera.js"></script>
	<script>
	   var map;
	   var camCtr;
       function init()
       {
       	 map = MapConfig.createMap("map");
       	 camCtr=new OpenLayers.CameraEditorCtr(map);
       	 inputInit();
       }
       function inputInit(){
			$("#xval").attr("value",camCtr.camera.states[0].x);
			$("#xval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#xval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].x = Number(val);
					toSphere();
					camCtr.update();
				}
			});
			$("#yval").attr("value",camCtr.camera.states[0].y);
			$("#yval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#yval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].y = Number(val);
					toSphere();
					camCtr.update();
				}
			});
			
			$("#longi").attr("value",camCtr.camera.states[0].y);
			$("#longi").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#longi").attr("value");
				if(re.test(val)){
					toMecator();
					camCtr.camera.states[0].x = Number($("#xval").val());
					camCtr.update();
				}
			});
			
			$("#lat").attr("value",camCtr.camera.states[0].y);
			$("#lat").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#lat").attr("value");
				if(re.test(val)){
					toMecator();
					camCtr.camera.states[0].y = Number($("#yval").val());
					camCtr.update();
				}
			});
			toSphere();
			$("#zval").attr("value",camCtr.camera.states[0].z);
			$("#zval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#zval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].z = Number(val);
					camCtr.update();
				}
			});
			$("#panval").attr("value",camCtr.camera.states[0].pan);
			$("#panval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#panval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].pan = Number(val);
					camCtr.update();
				}
			});
			$("#tiltval").attr("value",camCtr.camera.states[0].tilt);
			$("#tiltval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#tiltval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].tilt = Number(val);
					camCtr.update();
				}
			});
			$("#focalval").attr("value",camCtr.camera.states[0].focal);
			$("#focalval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#focalval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].focal = Number(val);
					camCtr.update();
				}
			});
			$("#cwval").attr("value",camCtr.camera.states[0].ccdwidth);
			$("#cwval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#cwval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].ccdwidth = Number(val);
					camCtr.update();
				}
			});
			$("#chval").attr("value",camCtr.camera.states[0].ccdheight);
			$("#chval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#chval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].ccdheight = Number(val);
					camCtr.update();
				}
			});
			$("#maxdist").attr("value",camCtr.camera.states[0].maxdist);
			$("#maxdist").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#maxdist").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].maxdist = Number(val);
					camCtr.update();
				}
			});
			map.events.register("click",map,function(e){
				 var lonlat = map.getLonLatFromViewPortPx(e.xy);
				 var point = MapConfig.rTransform(new OpenLayers.Geometry.Point(
			           lonlat.lon,lonlat.lat));
				 camCtr.camera.states[0].x = point.x;
				 camCtr.camera.states[0].y = point.y;
				 camCtr.update();
				 $("#xval").attr("value",camCtr.camera.states[0].x);
				 $("#yval").attr("value",camCtr.camera.states[0].y);
				 toSphere();
			});

			var typeChange = function(){
				camCtr.camera.type = Number($("#type option:selected'").attr("value"));
				camCtr.camera.tstate = Number($("#state option:selected'").attr("value"));
				camCtr.update();
			} 
			$("#type").change(typeChange);
			$("#state").change(typeChange);
		}

		function changetextbox()
		{
			if(document.getElementById("type").value=="0")
			{
				document.getElementById("max_tilt").setAttribute("disabled","disabled");
				document.getElementById("min_tilt").setAttribute("disabled","disabled");
				document.getElementById("max_rota").setAttribute("disabled","disabled");
				document.getElementById("min_rota").setAttribute("disabled","disabled");
				document.getElementById("max_tilt").value="";
				document.getElementById("min_tilt").value="";
				document.getElementById("max_rota").value="";
				document.getElementById("min_rota").value="";
			}else if(document.getElementById("type").value=="1")
			{
				document.getElementById("max_tilt").removeAttribute("disabled");
				document.getElementById("min_tilt").removeAttribute("disabled");
				document.getElementById("max_rota").removeAttribute("disabled");
				document.getElementById("min_rota").removeAttribute("disabled");
				document.getElementById("max_rota").removeAttribute("readonly");
				document.getElementById("min_rota").removeAttribute("readonly");
				document.getElementById("max_tilt").value="";
				document.getElementById("min_tilt").value="";
				document.getElementById("max_rota").value="";
				document.getElementById("min_rota").value="";
			}else if(document.getElementById("type").value=="2")
			{
				document.getElementById("max_tilt").removeAttribute("disabled");
				document.getElementById("min_tilt").removeAttribute("disabled");
				document.getElementById("max_rota").removeAttribute("disabled");
				document.getElementById("min_rota").removeAttribute("disabled");
				document.getElementById("max_rota").value=360;
				document.getElementById("min_rota").value=0;
				document.getElementById("max_rota").setAttribute("readonly","readonly");
				document.getElementById("min_rota").setAttribute("readonly","readonly");
			}
		}

		function isNumberKey(evt)
	    {
	         var charCode = (evt.which) ? evt.which : evt.keyCode;
	         if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
	            return false;
             return true;
	    }

        //摄像机输入逻辑检查
	    function check()
	    {
	    	if($("#cameraid").val()=="")
		    {
		    	alert("请输入摄像机ID");
			    return false;
		    }
	    	else
	    	{
		    	var flag=true;
	    		$.ajax({
	    			 async:false,
	    			 cache:false, 
					 url:"../doMeasureAction.php?do=checkcameraid",
					 data:{id:$("#cameraid").val()}, 
					 type:"post", 
					 dataType:"text",
					 success:function(data)
					 {
					     if(data=="existed")
					     {
						     alert("摄像机已存在！");
						     flag = false;
					     }
				     }     
			        });
		        if(!flag)
			        return false;
		    }
		    if($("#cameraname").val()==""||$("#camerapvgip").val()==""||$("#cameraavpath").val()==""||$("#cameramaxfocal").val()==""||$("#cameraminfocal").val()==""||$("#max_dist").val()=="")
		    {
		    	alert("摄像机信息输入不完整");
			    return false;
			}
			if($("#max_tilt").val()-90>0||$("#min_tilt").val()-0<0)
			{
			    alert("摄像机俯仰角应大于0度小于90度，请重新设置摄像机俯仰角范围");
			    return false;	
		    }
		    if($("#min_tilt").val()-$("#max_tilt").val()>0)
		    {
			    alert("摄像机最小俯仰角大于最大俯仰角");
			    return false;
		    }
		    if($("#max_rota").val()-360>0||$("#min_rota").val()-0<0)
			{
			    alert("摄像机方位角应大于0度小于360度，请重新设置摄像机方位角范围");
			    return false;	
		    }
		    if($("#min_rota").val()-$("#max_rota").val()>0)
		    {
			    alert("摄像机最小方位角大于最大方位角");
			    return false;
		    }
		    if($("#min_focal").val()-$("#max_focal").val()>0)
		    {
			    alert("摄像机最小焦距大于最大焦距");
			    return false;
		    }
		    if($("#focalval").val()-$("#max_focal").val()>0||$("#focalval").val()-$("#min_focal").val()<0)
	        {
		        alert("摄像机焦距不正确");
		        return false;
	        }
            if($("#type").val()!=0)
            {
		        if($("#tiltval").val()-$("#max_tilt").val()>0||$("#tiltval").val()-$("#min_tilt").val()<0)
		        {
			        alert("摄像机俯仰角不正确");
			        return false;
		        }  
		        if($("#panval").val()-$("#max_rota").val()>0||$("#panval").val()-$("#min_rota").val()<0)
		        {
			        alert("摄像机方位角不正确");
			        return false;
		        } 
            } 
		}
		
		function  projSwitch() {
		  if($("#pswitch").val()=="显示经纬度"){
		  	$("#pswitch").val("显示投影");
		  	$("#mocator").hide();
		  	$("#sphere").show();
		  	
		  }else{
		  	$("#pswitch").val("显示经纬度");
		  	$("#mocator").show();
		  	$("#sphere").hide();
		  }
		}
		
		function toMecator(){
			var myLocation = new OpenLayers.Geometry.Point($("#longi").val(), $("#lat").val())
        					.transform('EPSG:4326', 'EPSG:900913');
        	$("#xval").val(myLocation.x);
        	$("#yval").val(myLocation.y);
		}
		
		function toSphere(){
			var myLocation = new OpenLayers.Geometry.Point($("#xval").val(), $("#yval").val())
        					.transform('EPSG:900913', 'EPSG:4326');
        	$("#longi").val(myLocation.x);
        	$("#lat").val(myLocation.y);
		}
	</script>
</head>
<body onload="init();changetextbox();">
	<div id="wrap">
		<div id="header">
			<h3>添加摄像机</h3>
		</div>

		<div id="item">
			<div id="leftitem">
				<div class="map" id="map"></div>
				<form action="doAdminAction.php?act=addCamera" method="post" onsubmit="return check()">
					<div class="cameraloctable">
						<table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#cccccc">
							<tr>
								<td align="right">摄像机类型</td>
								<td>
									<select name="type" id="type" onChange="changetextbox()">
										<option value="0">枪机</option>
										<option value="1">云台枪机</option>
										<option value="2">球机</option>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">摄像机状态</td>
								<td>
									<select name="state" id="state">
										<option value="0">正常</option>
										<option value="1">故障</option>
										<option value="2">偏移</option>
										<option value="3">拟建</option>
										<option value="4">在建</option>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">摄像机位置</td>
								<td>
									<div id="mocator">
									<label for="xval">x:</label>
									<input type="text" name="locationx" id="xval" /><br/>
									<label for="yval">y:</label>
									<input type="text" name="locationy" id="yval" />
									</div>
									<div  id="sphere" style="display: none">
									<label>经度:</label>
									<input  type="text" name="longi" id="longi" /><br/>
									<label>纬度:</label>
									<input  type="text" name="lat" id="lat" />									
									</div>
									<input id="pswitch" type="button"  value="显示经纬度" onclick="projSwitch()"/>
								</td>
							</tr>
							<tr>
								<td align="right">摄像机高度</td>
								<td>
									<input onkeypress="return isNumberKey(event)" type="text" name="height" id="zval" placeholder="请输入摄像机高度"/>
								</td>
							</tr>
							<tr>
								<td align="right">ccd宽度</td>
								<td>
									<input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" type="text" id="cwval" name="ccd_width" placeholder="请输入ccd宽度"/>
								</td>
							</tr>
							<tr>
								<td align="right">ccd高度</td>
								<td>
									<input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" type="text" id="chval" name="ccd_height" placeholder="请输入ccd高度"/>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="camerainfotable">
					<table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#cccccc">
						<tr>
							<td align="right">摄像机ID</td>
							<td>
								<input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" type="text" name="id" id="cameraid" placeholder="请输入摄像机ID"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机名称</td>
							<td>
								<input type="text" name="name" id="cameraname" placeholder="请输入摄像机名称"/>
							</td>
						</tr>
						<tr>
							<td align="right">PVG IP</td>
							<td>
								<input type="text" name="pvgip" id="camerapvgip" placeholder="请输入PVG IP"/>
							</td>
						</tr>
						<tr>
							<td align="right">av路径</td>
							<td>
								<input type="text" name="avpath" id="cameraavpath" placeholder="请输入av路径"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机方位角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="pan" id="panval" placeholder="请输入摄像机旋转角"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机俯仰角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="tilt" id="tiltval" placeholder="请输入摄像机俯仰角"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机焦距</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="focal" id="focalval" placeholder="请输入摄像机焦距"/>
							</td>
						</tr>
						<tr>
							<td align="right">最大俯仰角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="max_tilt" id="max_tilt" placeholder="请输入最大俯仰角"/>
							</td>
						</tr>
						<tr>
							<td align="right">最小俯仰角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="min_tilt" id="min_tilt" placeholder="请输入最小俯仰角"/>
							</td>
						</tr>
						<tr>
							<td align="right">最大方位角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="max_rota" id="max_rota" placeholder="请输入最大旋转角"/>
							</td>
						</tr>
						<tr>
							<td align="right">最小方位角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="min_rota" id="min_rota" placeholder="请输入最小旋转角"/>
							</td>
						</tr>
						<tr>
							<td align="right">最大焦距</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="max_focal" id="cameramaxfocal" placeholder="请输入最大焦距"/>
							</td>
						</tr>
						<tr>
							<td align="right">最小焦距</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="min_focal" id="cameraminfocal" placeholder="请输入最小焦距"/>
							</td>
						</tr>
						<tr>
							<td align="right">最大可视距离</td>
							<td>
								<input onkeypress="return isNumberKey(event)" id="max_dist" type="text" name="max_length" placeholder="请输入最大可视距离"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机建设单位</td>
							<td>
								<input id="const_org" type="text" name="const_org" placeholder="请输入摄像机建设单位"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机使用单位</td>
							<td>
								<input id="use_org" type="text" name="use_org" placeholder="请输入摄像机使用单位"/>
							</td>
						</tr>
						<tr>
							<td align="right">摄像机建设时间</td>
							<td>
								<input id="const_time" type="text" name="const_time" placeholder="请输入摄像机建设时间"/>
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<input type="submit" value="添加摄像机" style="width:90px;height:35px"/>
							</td>
						</tr>
					</table>
				</div>
			</form>
			</div>
		</div>
</body>
</html>