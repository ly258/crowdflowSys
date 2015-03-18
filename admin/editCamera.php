<?php 
require_once '../include.php';
//checklogined();
if(!$_GET['id'])
{
    die("非法ID");
}
$where="WHERE videocms_camera.id='".$_GET['id']."'";
$sql = <<<EOF
	       SELECT videocms_camera.id as id,name,pvgip,avpath,type,state,ST_X(location) as x,ST_Y(location) as y,height,
           ccd_width,ccd_height,pan,tilt,focal,max_focal,min_focal,max_length,
		   const_org,use_org,const_time,max_tilt,min_tilt,max_rota,min_rota	   
		   FROM videocms_camera LEFT OUTER JOIN videocms_cameraadjust
	       ON videocms_camera.id = videocms_cameraadjust.id {$where}
EOF;
//echo $sql;
$result=fetchOne($link, $sql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Insert title here</title>
	<link rel="stylesheet" href="styles/addCamera.css">
	<script type="text/javascript" src="./scripts/OpenLayers.debug.js"></script>
	<script type="text/javascript" src ="./scripts/jquery-1.11.2.min.js"></script>
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
    	   camCtr.camera.states[0].x = Number($("#xval").attr("value"));
    	   camCtr.camera.states[0].y = Number($("#yval").attr("value"));
    	   camCtr.camera.states[0].z = Number($("#zval").attr("value"));
    	   camCtr.camera.states[0].pan = Number($("#panval").attr("value"));
    	   camCtr.camera.states[0].tilt = Number($("#tiltval").attr("value"));
    	   camCtr.camera.states[0].focal = Number($("#focalval").attr("value"));
    	   camCtr.camera.states[0].ccdwidth = Number($("#cwval").attr("value"));
    	   camCtr.camera.states[0].ccdheight = Number($("#chval").attr("value"));
    	   camCtr.camera.states[0].maxdist = Number($("#max_length").attr("value"));
    	   var typeChange = function(){
				camCtr.camera.type = Number($("#type option:selected'").attr("value"));
				camCtr.camera.tstate = Number($("#state option:selected'").attr("value"));
				camCtr.update();
			} 
    	   typeChange();
    	   camCtr.update();
    	   map.setCenter(new OpenLayers.LonLat(camCtr.camera.states[0].x,camCtr.camera.states[0].y));
    	   
			$("#xval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#xval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].x = Number(val);
					camCtr.update();
				}
			});
			$("#yval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#yval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].y = Number(val);
					camCtr.update();
				}
			});
			
			$("#zval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#zval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].z = Number(val);
					camCtr.update();
				}
			});
			
			$("#panval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#panval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].pan = Number(val);
					camCtr.update();
				}
			});
			
			$("#tiltval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#tiltval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].tilt = Number(val);
					camCtr.update();
				}
			});
			
			$("#focalval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#focalval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].focal = Number(val);
					camCtr.update();
				}
			});
			
			$("#cwval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#cwval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].ccdwidth = Number(val);
					camCtr.update();
				}
			});
			
			$("#chval").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#chval").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].ccdheight = Number(val);
					camCtr.update();
				}
			});
			
			$("#max_length").keyup(function(){
				var re = /^[0-9]+.?[0-9]*$/; 
				var val = $("#max_length").attr("value");
				if(re.test(val)){
					camCtr.camera.states[0].maxdist = Number(val);
					camCtr.update();
				}
			});
			map.events.register("click",map,function(e){
				 var lonlat = map.getLonLatFromViewPortPx(e.xy);
				 camCtr.camera.states[0].x = lonlat.lon;
				 camCtr.camera.states[0].y = lonlat.lat;
				 camCtr.update();
				 $("#xval").attr("value",camCtr.camera.states[0].x);
				 $("#yval").attr("value",camCtr.camera.states[0].y);
			});

			
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
	</script>
</head>
<body onload="init();changetextbox();">
	<div id="wrap">
		<div id="header">
			<h3>编辑摄像机</h3>
		</div>

		<div id="item">
			<div id="leftitem">
				<div class="map" id="map"></div>
				<form action="doAdminAction.php?act=editCamera" method="post">
					<div class="cameraloctable">
						<table width="100%" border="1" cellpadding="1" cellspacing="0" bgcolor="#cccccc">
							<tr>
								<td align="right">摄像机类型</td>
								<td>
									<select name="type" id="type" onChange="changetextbox()">
										<option value="0" <?php if($result['type']==0)echo "selected=true";?>>枪机</option>
										<option value="1" <?php if($result['type']==1)echo "selected=true";?>>云台枪机</option>
										<option value="2" <?php if($result['type']==2)echo "selected=true";?>>球机</option>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">摄像机状态</td>
								<td>
									<select name="state" id="state">
										<option value="0" <?php if($result['state']==0)echo "selected=true";?>>正常</option>
										<option value="1" <?php if($result['state']==1)echo "selected=true";?>>故障</option>
										<option value="2" <?php if($result['state']==2)echo "selected=true";?>>偏移</option>
										<option value="3" <?php if($result['state']==3)echo "selected=true";?>>拟建</option>
										<option value="4" <?php if($result['state']==4)echo "selected=true";?>>在建</option>
									</select>
								</td>
							</tr>
							<tr>
								<td align="right">摄像机位置</td>
								<td>
									<label for="xval">x</label>
									<input onkeypress="return isNumberKey(event)" type="text" name="locationx" id="xval" value="<?php echo $result['x'];?>" />
									<label for="yval">y</label>
									<input onkeypress="return isNumberKey(event)" type="text" name="locationy" id="yval" value="<?php echo $result['y'];?>" />
								</td>
							</tr>
							<tr>
								<td align="right">摄像机高度</td>
								<td>
									<input onkeypress="return isNumberKey(event)" type="text" name="height" id="zval" value="<?php echo $result['height'];?>" />
								</td>
							</tr>
							<tr>
								<td align="right">ccd宽度</td>
								<td>
									<input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" type="text" id="cwval" name="ccd_width" value="<?php echo $result['ccd_width'];?>" />
								</td>
							</tr>
							<tr>
								<td align="right">ccd高度</td>
								<td>
									<input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" type="text" id="chval" name="ccd_height" value="<?php echo $result['ccd_height'];?>" />
								</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="camerainfotable">
					<table width="500" border="1" cellpadding="1" cellspacing="0" bgcolor="#cccccc">
						<tr>
							<td align="right" width="120" height="20">摄像机ID</td>
							<td>
								<input onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')" type="text" name="id" value="<?php echo $result['id'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">摄像机名称</td>
							<td>
								<input type="text" name="name" value="<?php echo $result['name'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">PVG IP</td>
							<td>
								<input type="text" name="pvgip" value="<?php echo $result['pvgip'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">av路径</td>
							<td>
								<input type="text" name="avpath" value="<?php echo $result['avpath'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">摄像机方位角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="pan" id="panval" value="<?php echo $result['pan'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">摄像机俯仰角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="tilt" id="tiltval" value="<?php echo $result['tilt'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">摄像机焦距</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="focal" id="focalval" value="<?php echo $result['focal'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">最大俯仰角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="max_tilt" id="max_tilt" value="<?php echo $result['max_tilt'];?>" />
							</td>
						</tr>
						
						<tr>
							<td align="right">最小俯仰角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="min_tilt" id="min_tilt" value="<?php echo $result['min_tilt'];?>" />
							</td>
						</tr>
						 
						<tr>
							<td align="right">最大方位角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="max_rota" id="max_rota" value="<?php echo $result['max_rota'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">最小方位角</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="min_rota" id="min_rota" value="<?php echo $result['min_rota'];?>" />
							</td>
						</tr>
						
						<tr>
							<td align="right">最大焦距</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="max_focal" value="<?php echo $result['max_focal'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">最小焦距</td>
							<td>
								<input onkeypress="return isNumberKey(event)" type="text" name="min_focal" value="<?php echo $result['min_focal'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">最大可视距离</td>
							<td>
								<input onkeypress="return isNumberKey(event)" id="max_length" type="text" name="max_length" value="<?php echo $result['max_length'];?>" />
							</td>
						</tr>
						
						<tr>
							<td align="right">摄像机建设单位</td>
							<td>
								<input id="const_org" type="text" name="const_org" value="<?php echo $result['const_org'];?>" />
							</td>
						</tr>
						
						<tr>
							<td align="right">摄像机使用单位</td>
							<td>
								<input id="use_org" type="text" name="use_org" value="<?php echo $result['use_org'];?>" />
							</td>
						</tr>
						<tr>
							<td align="right">摄像机建设时间</td>
							<td>
								<input id="const_time" type="text" name="const_time" value="<?php echo $result['const_time'];?>" />
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<input type="submit" value="提交修改" style="width:90px;height:35px"/>
							</td>
						</tr>
					</table>
				</div>
			</form>
			</div>
		</div>
</body>
</html>