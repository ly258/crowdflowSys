/********************************************************************************************************
Camara.js
基于openlayers的摄像机渲染库
create by sunkaixin
2014/10/16
*********************************************************************************************************/
//
OpenLayers.Icon.prototype.rotate = function (angle){
	this.imageDiv.style.cssText += "-moz-transform: rotate("+angle+"deg);"+
	                       "-webkit-transform: rotate("+angle+"deg);"+ 
						   "-o-transform: rotate("+angle+"deg);"+ 
						   "-ms-transform: rotate("+angle+"deg);"+
						   "transform:rotate("+angle+"deg);";
};
//摄像机类型
OpenLayers.CameraType = {
	GUNCAMERA:0, //枪机
	THBCAMRA:1,    //云台枪机
	BOLLCAMERA:2, //球机
};
//摄像机状态
OpenLayers.CameraType = {
	NOMARL:0,  //正常
	BREAK:1,   //故障
	MOVE:2,    //偏移 
	PLAN:3,    //拟建
	BULIDING:4  //在建
};

//摄像机状态
OpenLayers.FovType = {
	CURRENT:0,
	FULL:1,
	REAL:2,
};
//摄像机类
OpenLayers.Camera = OpenLayers.Class({
    id:null,
    index:null,
	name:null,
    ip:null,
    avpath:null,
	marker:null,
	track:null,
	fov:null,
	type:0,  //类型
	tstate:0, //所处摄像机状态
	states:null,
	minfocal:1000,
	maxfocal:1000,
	minpan:0,
	maxpan:0,
	mintilt:0,
	maxtilt:0,
	vurl:null,
	constOrg:null,
	useOrg:null,
	constTime:null,
	number:null,
	peoplePosition:null,
	fovType:OpenLayers.FovType.CURRENT,
	fovQueryUrl:"fovQuery.php",
	render:null,
	_cState:0,
	initialize:function(states,vurl){
		this.states = states;
		this.vurl = vurl;
		//this.changeState();
	},
	sortState:function(){
		if(!states) return;
		
		this.cameras.sort(function(a,b){ return a.time<b.time;});
	},
	calcFov:function(){
		var state = this.getState();
		state.calcFov();
		this.fov = (this.fov !=null) ? new OpenLayers.Feature.Vector(state.fov,null,this.fov.style)
			               :new OpenLayers.Feature.Vector(state.fov);
		this.fov.attributes = {
			peonum: this.number = (this.number==-1) ? "" : this.number,
		};
		/*
		if(this.fovType == OpenLayers.FovType.CURRENT){
			var state = this.getState();
			state.calcFov();
			this.fov = (this.fov !=null) ? new OpenLayers.Feature.Vector(state.fov,null,this.fov.style)
			               :new OpenLayers.Feature.Vector(state.fov);
		}else{
			var fov =  this.calcFullOrRealFov(this.fovType);	
			this.fov = new OpenLayers.Feature.Vector(fov);
		}
		*/
	},
	calcTrack:function(){
		var points = new Array();
		var style = {
			strokeColor: "#0000FF",
			strokeWidth: 3,
			strokeDashstyle: "solid",
			pointRadius: 6,
			pointerEvents: "visiblePainted"
		}; 
		for(var i = 0;i < this.states.length;i++){		
			var ccamera = this;
			points.push(new OpenLayers.Geometry.Point(this.states[i].x
				                              ,this.states[i].y));
		}
		
		//变换		                           
		this.track = new OpenLayers.Feature.Vector(
			            MapConfig.transform(new OpenLayers.Geometry.LineString(points)),
			            null,
			            style);
	},
	calcFullOrRealFov:function(type){
		var fov = null;
		var camera = this;
		$.ajax({
			type:'GET',
			url:this.fovQueryUrl,
			data:{"type":((type==OpenLayers.FovType.FULL) ? "full":"real")
				,"id":this.id},
			dataType:'json',
			success:function(data){				
				var geojson = new OpenLayers.Format.GeoJSON();
				fov = geojson.read(data); 
				camera._markerCreate();
				//变换
				fov[0].geometry = MapConfig.transform(fov[0].geometry);
				camera.fov = fov[0];
				camera.render.renderCamera(camera);  
			},
			error:function(){
				
			}
		});
		
		return (fov) ? fov[0].geometry : null;
	},
	//修正时间
	getState:function(){
		var time = this.time;
			
		var cState = this._cState;
		if(cState > 0
		  &&this.states.length > cState
		  && time != null){
			var state = new OpenLayers.Camera.State();
			var curstate = this.states[cState-1];
			var nextState = this.states[cState];
			var rate = (nextState.time-time)/
		    (nextState.time-curstate.time);
		    state.x = curstate.x*rate+nextState.x*(1-rate);
		    state.y = curstate.y*rate+nextState.y*(1-rate);
		    state.z = curstate.z*rate+nextState.z*(1-rate);
		    state.pan = curstate.pan*rate+nextState.pan*(1-rate);
		    state.tilt = curstate.tilt*rate+nextState.tilt*(1-rate);
		    state.focal = curstate.focal*rate+nextState.focal*(1-rate);
		    state.ccdwidth = curstate.ccdwidth;
		    state.ccdheight = curstate.ccdheight;
		    state.maxdist = curstate.maxdist;
		    return state;
		}
		
		return (!cState)? this.states[cState] : this.states[cState-1];
	},
	setTime:function(time){
		if(time == null) return;
		this.time = time;
	    var cState = 0;
		var ctime = time;
		while(this.states[cState].time < ctime){
			cState++;
		}
		this._cState = cState;
		this.changeState();
	},
	next:function(time,step){
		
		if(time == null) return;
		this.time = time;
		
		var cState = this._cState;
		
		var ctime = time+step;
		while(cState<this.states.length
		&&this.states[cState].time < ctime){
			cState++;
		}
		
		this._cState = cState;
		
		this.changeState();
	},
	changeState:function(){
		if(this.fovType == OpenLayers.FovType.CURRENT){
			this._markerCreate();
			this.calcFov();	
			this.render.renderCamera(this);
		}else{
			this.calcFullOrRealFov(this.fovType);
		}
		
	},
	_markerCreate:function(){
		var state = this.getState();
		var icType = Number(this.type*5)+Number(this.tstate);
		var	path = "./images/"+icType+".png";
		var roffset = 0;
		var pan = state.pan;
		if(icType >= 0 && icType < 10){	
			roffset =pan-90;
		}
		
		var icon = new OpenLayers.Icon(path);	
		icon.rotate(roffset);
		var y = icon.size.h/2;
		var offsetX = y*Math.sin(pan*Math.PI/180);
		var offsetY = y*Math.cos(pan*Math.PI/180);
		icon.offset = new OpenLayers.Pixel(-icon.size.h/2-offsetX,
				                           -icon.size.w/2+offsetY);
		//变换
		var pt = MapConfig.transform(new OpenLayers.Geometry.
			                           Point(state.x, state.y));		                           
		this.marker = new OpenLayers.Marker(
							new OpenLayers.LonLat(
							  pt.x, pt.y)
							  ,icon);		
	}
});

OpenLayers.Camera.State = OpenLayers.Class({
	initialize:function(){

	},
	id:0,
	x:0,
	y:0,
	z:0,
	time:null,
	startTime:null,
	endTime:null,
	focal:1000,
	pan:0,
	tilt:0,
	ccdwidth:0,
	ccdheight:0,
	fov:null,
	fullFov:null,
	maxdist:300,
	calcFov:function(){
		ptsArr = new Array();
		var yAngle = Math.atan(this.ccdheight/this.focal*2);
		//近距离两点
		var cy = Math.tan(this.tilt*Math.PI/180-yAngle)*this.z;
		var fRay = Math.sqrt(cy*cy+this.z*this.z);
		var width = fRay*this.ccdwidth/this.focal;
		ptsArr.push(new OpenLayers.Geometry.Point(-width,cy));
		ptsArr.push(new OpenLayers.Geometry.Point(width,cy));
		//远距离两点 
		cy = Math.tan(this.tilt*Math.PI/180+yAngle)*this.z;
		cy = (cy < 0 || cy > this.maxdist) ? this.maxdist : cy; 
		fRay = Math.sqrt(cy*cy+this.z*this.z);
		width = fRay*this.ccdwidth/this.focal;
		ptsArr.push(new OpenLayers.Geometry.Point(width,cy));
		ptsArr.push(new OpenLayers.Geometry.Point(-width,cy));
		//旋转
		for(var i =0 ;i < ptsArr.length;i++){
			var x = ptsArr[i].x;
			var y = ptsArr[i].y;
			ptsArr[i].x = x*Math.cos(-this.pan*Math.PI/180)-y*Math.sin(-this.pan*Math.PI/180)+this.x;
			ptsArr[i].y = x*Math.sin(-this.pan*Math.PI/180)+y*Math.cos(-this.pan*Math.PI/180)+this.y;
		}
		//变换
		this.fov = MapConfig.transform(new OpenLayers.Geometry.Polygon(
			      new OpenLayers.Geometry.LinearRing(ptsArr)));
	},
	calcFullFov:function(){
	}
});
//摄像机解析
OpenLayers.Camera.Prase=function(cGeoJson){
		var geojson = new OpenLayers.Format.GeoJSON();
		var featureCollection = geojson.read(cGeoJson);
		var cameras = new Array();
		for(var i = 0;i < cGeoJson.features.length;i++){
			var cameraState = new OpenLayers.Camera.State();
			var camState = new OpenLayers.Camera.State();
			camState.x = featureCollection[i].geometry.x;
			camState.y = featureCollection[i].geometry.y;
			camState.z = featureCollection[i].data.height;
			camState.pan = featureCollection[i].data.pan;
			camState.tilt = featureCollection[i].data.tilt;
			camState.focal = featureCollection[i].data.focal;
			camState.ccdwidth = featureCollection[i].data.ccdwidth;
			camState.ccdheight = featureCollection[i].data.ccdheight;
			camState.maxdist = featureCollection[i].data.maxdist;
			var camera = new OpenLayers.Camera([camState],"");
			camera.id = featureCollection[i].data.id;
			camera.name = featureCollection[i].data.name;
			camera.ip = featureCollection[i].data.ip;
			camera.avpath = featureCollection[i].data.avpath;
			camera.type = featureCollection[i].data.type;
			camera.tstate = featureCollection[i].data.state;
			camera.minpan = featureCollection[i].data.minpan;
			camera.maxpan = featureCollection[i].data.maxpan;
			camera.mintilt = featureCollection[i].data.mintilt;
			camera.maxtilt = featureCollection[i].data.maxtilt;
			camera.minfocal = featureCollection[i].data.minfocal;
			camera.maxfocal = featureCollection[i].data.maxfocal;
			camera.constOrg = featureCollection[i].data.constorg;
			camera.useOrg = featureCollection[i].data.useorg;
			camera.constTime = featureCollection[i].data.consttime;
			cameras.push(camera);
		}
		return cameras;
};
//Camera集合
OpenLayers.CameraCollection = OpenLayers.Class({
	map:null,
	_fovLayer:null,
	_trackLayer:null,
	_cameraLayer:null,
	_cameras:null,
	_time:null,
	mediante:null,
	lastSelect:null,
	initialize:function(cameras){
		this._cameras = cameras;
		this._fovLayer = new OpenLayers.Layer.Vector("FOV");
		this._trackLayer = new OpenLayers.Layer.Vector("轨迹");
		this._cameraLayer = new OpenLayers.Layer.Markers("摄像头");		
	},
	attach:function(map){
		this.map = map;
		map.addLayers([this._fovLayer,this._trackLayer,this._cameraLayer]);
		this._render();
	},
	detach:function(){
		var map = this.map;
		if(!map) return;
		map.removeLayer(this._fovLayer);
		map.removeLayer(this._trackLayer);
		map.removeLayer(this._cameraLayer);
	},
	setCameras:function(cameras){
		this._cameras = cameras;
	},
	getCameras:function(){
		return this._cameras;
	},
	_render:function(){
		this._cameraLayer.clearMarkers();
		this._fovLayer.removeAllFeatures();
		for(var i = 0;i < this._cameras.length;i++){
			var ccamera = this._cameras[i];
			ccamera.render = this;
			ccamera.changeState();		
		}	
	},
	renderCamera:function(camera){
		var mediante = this.mediante;
		this._cameraLayer.addMarker(camera.marker);
			
		camera.popup = null;
		camera.marker.camera = camera;		
		camera.marker.events.register("click",camera.marker,function(evt){	
				mediante.select(this.camera,true);  
		});
		this._fovLayer.addFeatures([camera.fov]);
	},
	info:function(ccamera){
		 var map = this.map;
		 var typeName,stateName;

		switch(ccamera.type){
			case 0:
				typeName ="枪机";
				break;
			case 1:
				typeName ="云台枪机";
				break;
			case 2:
				typeName ="球机";
				break;

		}

		switch(ccamera.tstate){
			case 0:
				stateName ="正常";
				break;
			case 1:
				stateName ="故障";
				break;
			case 2:
				stateName ="偏移";
				break;
			case 3:
				stateName ="拟建";
				break;
			case 4:
				stateName ="在建";
				break;
		}
		 var popup = new OpenLayers.Popup.FramedCloud(ccamera.id,   
                                    ccamera.marker.lonlat,  
                                    null, 
								   "<b>"+ccamera.id+"</b><br/><hr/>"	
                                    +"<b>名称:</b>"+ccamera.name+"<br/>"
									+"<b>类型:</b>"+typeName+"&nbsp;&nbsp;<b>状态:</b>"+stateName+"<br/>"
									+"<b>PVG ip:</b>"+ccamera.ip+"<br>"
									+"<b>AV路径:</b>"+ccamera.avpath+"<br>"
									+"<b>X:</b>"+ccamera.states[0].x+"&nbsp;<b>Y:</b>"+ccamera.states[0].y+"&nbsp;<b>Z:</b>"+ccamera.states[0].z+"<br/>"
									+"<b>方位角 </b> <b>当前:</b>"+ccamera.states[0].pan+"&nbsp;<b>最大:</b>"+ccamera.maxpan+"&nbsp;<b>最小:</b>"+ccamera.minpan+"<br/>"
									+"<b>俯仰角 当前</b>:"+ccamera.states[0].tilt+"&nbsp;<b>最大:</b>"+ccamera.maxtilt+"&nbsp;<b>最小:</b>"+ccamera.mintilt+"<br/>"
									+"<b>焦距 当前</b>:"+ccamera.states[0].focal+"&nbsp;<b>最大:</b>"+ccamera.maxfocal+"&nbsp;<b>最小:</b>"+ccamera.minfocal+"<br/>"
									+"<b>分辨率:</b>"+ccamera.states[0].ccdwidth+"x"+ccamera.states[0].ccdheight+"<br/>"
									+"<b>建设单位:</b>"+ccamera.constOrg+"<br/>"
									+"<b>使用单位:</b>"+ccamera.useOrg+"<br/>"
									+"<b>建设时间:</b>"+ccamera.constTime+"<br/>",
									null,									
                                    true);  
                    popup.setBorder("#999999 solid 1px"); 
					map.addPopup(popup);
					//清空popup
				 	while(map.popups.length >0){
						map.removePopup(map.popups[0]);
					}			
          map.addPopup(popup);
		   //
		  var style = {
			strokeColor: "#00FF00",
            strokeOpacity: 1,
            strokeWidth: 3,
            fillColor: "#00FF00",
            fillOpacity: 0.5,
            pointRadius: 6,
            pointerEvents: "visiblePainted"		  
		  };
		  
		  //交换状态
		  if(this.lastSelect!=null){
			this.lastSelect.fov.style = ccamera.fov.style;		
		  }
		  
		  ccamera.fov.style = style;
		 this.lastSelect = ccamera;
		  
		  
		  this._fovLayer.redraw();
          //console.log(map.popups);
          //OpenLayers.Event.stop(evt);
	},
	update:function(){
		this._fovLayer.removeAllFeatures();
		this._trackLayer.removeAllFeatures();
		this._cameraLayer.clearMarkers();
		this._render();
	},
	setTime:function(time){
		this._fovLayer.removeAllFeatures();
		this._trackLayer.removeAllFeatures();

		this._time = time;
		for(var i = 0;i < this._cameras.length;i++){
			this._cameraLayer.removeMarker(this._cameras[i].marker);
			this._cameras[i].setTime(this._time);
		}
		this._render();
	},
	next:function(step){
		this._fovLayer.removeAllFeatures();
		this._trackLayer.removeAllFeatures();

		this._time.setTime(this._time.getTime()+step);
		for(var i = 0;i < this._cameras.length;i++){
			this._cameraLayer.removeMarker(this._cameras[i].marker);
			this._cameras[i].next(this._time,step);
		}
		this._render();
	}
});

//摄像机编辑控件
OpenLayers.CameraEditorCtr = OpenLayers.Class({
	cameraCollection:null,
	camera:null,
	map:null,
	initialize:function(map){
		this.map = map;
		var camState = new OpenLayers.Camera.State();
		var point = MapConfig.rTransform(new OpenLayers.Geometry.Point(
			           map.getCenter().lon,map.getCenter().lat));
		camState.x = point.x;
		camState.y = point.y;
		camState.z = 10;
		camState.pan = 30;
		camState.tilt = 90;
		camState.focal = 2000;
		camState.ccdwidth = 600;
		camState.ccdheight = 480;
		this.camera = new OpenLayers.Camera([camState],"");
		this.CameraCollection = new OpenLayers.CameraCollection([this.camera]);
		this.CameraCollection.attach(map);
	},
	update:function(){
		var point = MapConfig.transform(new OpenLayers.Geometry.Point(
			           this.camera.states[0].x,this.camera.states[0].y));
		this.map.setCenter(new OpenLayers.LonLat(point.x,point.y));
		this.CameraCollection.update();
	}
});

//摄像机列表控件
OpenLayers.CameraList = OpenLayers.Class({
	container:null,
	cameras:new Array(),
	mediante:null,  //中介者
	style:null,
	initialize:function(name){
		this.container = $("#"+name);
		//this.defaultStyle();
	},
	update:function(){	
		var container = this.container;
		var cameras = this.cameras;
		var cameraCollection = this.cameraCollection;
		
		container.empty();
		for(var i = 0;i < cameras.length;i++){
			
			var item = this.defaultStyle(i);
			cameras[i].index = i;
			container.append(item);			
		}

		var mediante = this.mediante;
		//定义点击函数
		$(".item").click(function(){		
			mediante.select(cameras[$(this).attr("id")]);			
		});
		//列表变色处理
 		$(".item").mouseover(function(){
				$(this).css("background-color","yellow");		 
		});

		$(".item").mouseout(function(){
				$(this).css("background-color","white");		  
	    });	
	},
	defaultStyle:function(i){
		var cameras = this.cameras;
		var typeName,stateName;

		switch(cameras[i].type){
				case 0:
					typeName ="枪机";
					break;
				case 1:
					typeName ="云台枪机";
					break;
				case 2:
					typeName ="球机";
					break;

		}

		switch(cameras[i].tstate){
				case 0:
					stateName ="正常";
					break;
				case 1:
					stateName ="故障";
					break;
				case 2:
					stateName ="偏移";
					break;
				case 3:
					stateName ="拟建";
					break;
				case 4:
					stateName ="在建";
					break;

		}
		var style= $("<div id="+i+" class=\"item\" style=\"width:100%;height:110px;cursor:pointer\">"
				+"<table width=\"100%\" height=\"100%\">"
				+"	<tr><td  width=\"40\" align=\"center\" align=\"center\" valign=\"center\">"+(i+1)+"</td>"
				+"     <td>"
				+"			<table width=\"100%\" height=\"100%\" style=\"font-size:12px\">"
				+"	    		<tr>"
				+"					<td colspan=\"3\" style=\"font-size:14px\">"+cameras[i].id+"</td>"
				+"				</tr>"
				+"				<tr>"
				+"				 	<td width=\"30\">名称:</td>"
				+"				 	<td colspan=\"3\">"+cameras[i].name+"</td>"
				+"				</tr>"
				+"				<tr>"
				+"				 	<th colspan=\"1\">当前人数:85</th>"
				+"                  <th colspan=\"1\">&nbsp&nbsp&nbsp统计曲线：</th>"
				+"                  <th colspan=\"1\">"
				+"                  	<select>"
				+"                      	<option>是</option>"
				+"                          <option>否</option>"
				+"                      </select>"
				+"                  </th>"
				+"				</tr>"
				+"			</table>"
				+"		</td>"
				+"		<td align=\"center\">"
				+"			<img src=\"./images/"+(cameras[i].type*5+cameras[i].tstate)+".png\" width=\"50\" height=\"40\"/>"
				+"		</td>"						
				+"	</tr>"
				+"</table>"
			+"</div>");
		return style;
	},
	select:function(camera,isScrollTo){
		$(".item").css("background-color","white");
		$(this).unbind("mouseout");
		$(".item").mouseout(function(){
				$(this).css("background-color","white");
			});

		 $(".item#"+camera.index+"").unbind("mouseout");						
		 $(".item#"+camera.index+"").css("background-color","yellow");
		 $(".item#"+camera.index+"").mouseout(function(){
				$(this).css("background-color","yellow");
		});
		
		//滚动条滚动
		if(isScrollTo){
			var container = this.container;
			var scrollTo = $(".item#"+camera.index+"");
			container.scrollTop(
				scrollTo.offset().top - container.offset().top + container.scrollTop()
			);
		}
		
	}
});
//CameraQueryer
//摄像机查询分析器
OpenLayers.CameraQueryer = OpenLayers.Class({
	map:null,
	selectLayer:null,
	drawControls:null,
	mediante:null,  //中介者
	queryurl:"cameraQuery.php",
	MODE:{
		POINT:"point",
		RECT:"rect",
		POLYGON:"POLYGON"
	},
	initialize:function(){
		var selectLayer = new OpenLayers.Layer.Vector("selectLayer",{
				    styleMap: new OpenLayers.StyleMap({'default':{
                    strokeColor: "#00FFFF",
                    strokeOpacity: 1,
                    strokeWidth: 3,
                    fillColor: "#00FFFF",
                    fillOpacity: 0.1,
                    pointRadius: 6,
                    pointerEvents: "visiblePainted"
                }})
		});
		this.selectLayer = selectLayer;
		var drawControls = {
			rect:new OpenLayers.Control.DrawFeature(selectLayer,
                        OpenLayers.Handler.RegularPolygon, {
                        handlerOptions: {
                                sides: 4,
                                irregular: true
           }}),
			polygon:new OpenLayers.Control.DrawFeature(selectLayer,
                          OpenLayers.Handler.Polygon)
        };
		
	    var queryer = this;
		for(var key in drawControls) {
			drawControls[key].events.register("featureadded",drawControls[key],function (e){
				queryer.selectLayer.removeAllFeatures();
				queryer.selectLayer.addFeatures([e.feature]);
				queryer.query();
			});
        }
	
		this.drawControls = drawControls;
	},
	setQueryMode:function(mode){
		this.selectLayer.removeAllFeatures();
		var drawControls = this.drawControls;
        for(key in drawControls) {
            var control = drawControls[key];
            if(mode == key) {
                control.activate();
            } else {
                control.deactivate();
            }
        }
	},
	query:function(queryString){
		var selectLayer = this.selectLayer;
		var features = selectLayer.features;
		var GeoString = "";
		if(features.length > 0){
			var geojson = new OpenLayers.Format.GeoJSON();
			
			GeoString = geojson.write(MapConfig.rTransform(
				                selectLayer.features[0].geometry));
		}
								
		var searchJson = {"type":"Feature",
					"geometry":(!GeoString) ? "" : eval("("+GeoString+")"),
					"properties":{
						"searchString":searchTxt.value
			}
		};
		
		var mediante = this.mediante;
		//var url = this.queryurl;
		//alert(JSON.Strinfy(searchJson));
		$.ajax({
				type:'POST',
				url:this.queryurl,
				data:searchJson,
				dataType:'json',
				success:function(data){
				    //$("#rsList").text(data);					
					var cameras = OpenLayers.Camera.Prase(data);
					mediante.setCameras(cameras);
				},
				error:function(){
					alert("请求异常！");
				}
		});
	},
	attach:function(map){
		map.addLayers([this.selectLayer]);
		for(var key in this.drawControls) {
            map.addControl(this.drawControls[key]);
		}
		this.map = map;
	},
	detach:function(){
		if(!this.map) return;
		
		this.map.removeLayer(this.selectLayer);
		for(var key in this.drawControls) {
			this.drawControls[key].deactivate();
            this.map.removeControl(this.drawControls[key]);
		}
		this.map = null;
	}
});

//cameraViewer
//一个摄像机展示总集合
OpenLayers.CameraViewer = OpenLayers.Class({
	cameraCollection:null,
	cameraQueryer:null,
	cameraList:null,
	cameras:null,
	initialize:function(cameraCollection,cameraList,cameraQueryer){
		this.cameraCollection = cameraCollection;
		this.cameraQueryer = cameraQueryer;
		this.cameraList = cameraList;
		cameraCollection.mediante = this;
		cameraList.mediante = this;
		cameraQueryer.mediante = this;
	},
	setCameras:function(cameras){
		this.cameraCollection.setCameras(cameras);
		this.cameraList.cameras = cameras;
		this.cameraCollection.update();
		this.cameraList.update();
	},
	select:function(camera,isScrollTo){
		this.cameraCollection.info(camera);
		this.cameraList.select(camera,isScrollTo);
	},
	attach:function(map){
		this.cameraQueryer.attach(map);
		this.cameraCollection.attach(map);
		this.cameraList.update();
	},
	detach:function(){
		this.cameraQueryer.detach();
		this.cameraCollection.detach();
		this.cameraList.container.empty();
	}
});

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
			};
			if(/(y+)/.test(format)) format=format.replace(RegExp.$1,
			(this.getFullYear()+"").substr(4- RegExp.$1.length));
			for(var k in o)if(new RegExp("("+ k +")").test(format))
			format = format.replace(RegExp.$1,
			RegExp.$1.length==1? o[k] :
			("00"+ o[k]).substr((""+ o[k]).length));
			return format;
};
