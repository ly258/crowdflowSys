/**
 * @author sunkaixin
 */
/**
 * 视频类
 * OpenLayers.Video
 */
OpenLayers.Video = OpenLayers.Class(OpenLayers.Camera,{
	step:40,
	relateCamera:"",
	startTime:null,
	endTime:null,
	initialize:function(states,vurl){
		this.states = states;
		this.vurl = vurl;
		this._markerCreate();
		this.calcFov();
	},
	_markerCreate:function(){
		var state = this.getState();
		//console.log(state.x+" "+state.y);
		
		var path = (this.marker != null) ? this.marker.icon.url : "./images/normal.ico";
		var icon = new OpenLayers.Icon(path);
		icon.offset = new OpenLayers.Pixel(-icon.size.w/2,-icon.size.h);
		this.marker = new OpenLayers.Marker(
							new OpenLayers.LonLat(
							  state.x, 
							  state.y)
							  ,icon);
	    //this.marker.draw();
	},
	changeState:function(){
		var state = this.getState();
		this.marker.lonlat.lon = state.x; 
		this.marker.lonlat.lat = state.y;
		
		var layer = this.fov.layer;
		if(layer == null)
		      return;
		      
		layer.removeFeatures([this.fov]);
		state.calcFov();
		this.fov.geometry = state.fov;
		//layer.redraw();
		this.calcFov();
		layer.addFeatures(this.fov);
		//
	},
	play:function(time){
		//setTimeout(this.play(time),this.step);
	}
});
/*
 *视频集合类 
 *OpenLayers.VideoCollection 
 * */
OpenLayers.VideoCollection = OpenLayers.Class(OpenLayers.CameraCollection,{
	videoPlayer:null,
	prefixurl:"",
	initialize:function(videos,videoPlayer){
		OpenLayers.CameraCollection.prototype.initialize.apply(this,[videos]);
		this.videoPlayer = 	videoPlayer;
		var coll = this;
		this.videoPlayer.addEventListener("loadedmetadata",function(){
			if(this.video == null)
			    return;
			    
			this.play(); 
		});
		//添加事件
		this.videoPlayer.addEventListener("play",function (){
			//var time = this.currentTime;
			if(this.video == null)
			    return;
			    
			this.defaultMuted=true;
			var realStartTime =  this.video.states[0].time-this.video.startTime;
			var realEndTime =  this.video.states[this.video.states.length-1].time-this.video.startTime;
			/*
			this.currentTime = (!isNaN(this.currentTime)&&this.currentTime > realStartTime && this.currentTime > realEndTime)
			                    ? this.currentTime : this.realStartTime;
			*/
			if(isNaN(this.currentTime)
			  ||this.currentTime < realStartTime
			  ||this.currentTime > realEndTime){
				this.currentTime = realStartTime;
			}
			
			this.video.setTime(this.currentTime+this.video.startTime);
		    $(this).parent().show();      
			this.playfun = function(){
				/*
				if(this.currentTime < realStartTime
				||this.currentTime > realEndTime){
					this.pause();
					return;
				}
				*/
				this.video.next(this.currentTime+this.video.startTime,0);
				coll._cameraLayer.redraw();
				//console.log("current:"+this.currentTime); 
			};
			
			clearInterval(this.playfun.bind(this),40);
			setInterval(this.playfun.bind(this),40);
		});
		//暂停去除事件
		this.videoPlayer.addEventListener("pause",function (){
			//var time = this.currentTime;
			if(this.video == null
			 ||this.playfun == null)
			    return;
			
			clearInterval(this.playfun.bind(this),40);
		});  	
	},
	_render:function(){
		var mediante = this.mediante;
		for(var i = 0;i < this._cameras.length;i++){
			var ccamera = this._cameras[i];
			ccamera.changeState();
		    this._cameraLayer.addMarker(ccamera.marker);
			
			ccamera.popup = null;
			ccamera.marker.camera = ccamera;
			var c = this;		
			ccamera.marker.events.register("click",ccamera.marker,function(evt){	
					mediante.select(this.camera,true);
					//c.play(this.camera);
					 
			});
			
			ccamera.marker.events.register("mouseover",ccamera.marker,function(evt){	
					//mediante.select(this.camera,true);
					//c.play(ccamera);
					$("#map").css("cursor","pointer");
					 
			});
			ccamera.marker.events.register("mouseout",ccamera.marker,function(evt){	
					//mediante.select(this.camera,true);
					//c.play(ccamera);
					$("#map").css("cursor","default");
					OpenLayers.Event.stop(evt);
					 
			});
			this._fovLayer.addFeatures([ccamera.fov]);
			
			//加入轨迹
			if(ccamera.states.length > 1){
				ccamera.calcTrack();
				this._trackLayer.addFeatures(ccamera.track);
			}
		}	
	},
	play:function(ccamera){ 
		this.videoPlayer.video = ccamera;
		this.videoPlayer.src = this.prefixurl+ccamera.vurl;
		
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
			this.lastSelect.marker.icon.url = "./images/normal.ico";
			this.lastSelect.marker.draw();		
		 }
		  
		 ccamera.fov.style = style;
		 ccamera.marker.icon.url = "./images/select.ico";
		 ccamera.marker.draw();
		 this.lastSelect = ccamera;
		  		  
		 this._fovLayer.redraw();
	}
}); 
/*
 * OpenLayers.Video.Prase
 * 解析VIDEO JSON
 * */
OpenLayers.Video.Prase = function(json){
	var videos = new Array();
	for(var i =0;i < json.length;i++){
		var states = new Array();
		for(var j = 0;j < json[i].states.length;j++){
			var state = new OpenLayers.Camera.State();
			state.x = Number(json[i].states[j].x);
			state.y = Number(json[i].states[j].y);
			state.z = Number(json[i].states[j].height);
			state.pan = Number(json[i].states[j].pan);
			state.tilt = Number(json[i].states[j].tilt);
			state.time = Number(json[i].states[j].time);
			state.focal = Number(json[i].states[j].focal);
			state.ccdwidth = Number(json[i].states[j].ccdwidth);
			state.ccdheight = Number(json[i].states[j].ccdheight);
			states.push(state);
		}
		var video = new OpenLayers.Video(states,json[i].url);
		video.id = json[i].id;
		video.name = json[i].name;
		video.relateCamera = json[i].relateCamera;
		video.startTime = Number(json[i].startTime);
		video.endTime = Number(json[i].endTime);
		videos.push(video);		
	}
	return videos;
};


//视频编辑控件
 OpenLayers.VideoEditorCtr = OpenLayers.Class(OpenLayers.CameraEditorCtr,{
 	selectId:-1,
	initialize:function(map,player){
		this.map = map;
		var camState = new OpenLayers.Camera.State();
		camState.x = map.getCenter().lon;
		camState.y = map.getCenter().lat;
		camState.z = 10;
		camState.pan = 30;
		camState.tilt = 90;
		camState.time = 0;
		camState.startTime = 0;
		camState.endTime = 0;
		camState.focal = 2000;
		camState.ccdwidth = 600;
		camState.ccdheight = 480;
		this.camera = new OpenLayers.Video([camState],"");
		this.camera.startTime = new Date();
		this.camera.endTime = new Date();
		this.CameraCollection = new OpenLayers.VideoCollection([this.camera],player);
		this.CameraCollection.parent = this;
		this.CameraCollection._render=function(){
			for(var i = 0;i < this._cameras[0].states.length;i++){
				var ccamera = this._cameras[0];
				ccamera._cState = i+1;
				ccamera._markerCreate();
				ccamera.calcFov();
				//
				/*
				if(i == this.parent.selectId){
					var style = {
						strokeColor: "#00FF00",
			            strokeOpacity: 1,
			            strokeWidth: 3,
			            fillColor: "#00FF00",
			            fillOpacity: 0.5,
			            pointRadius: 6,
			            pointerEvents: "visiblePainted"		  
					 };
		 			ccamera.marker.icon.url = "./images/select.ico";
		 			ccamera.fov.style = style;
		 		}
				*/
			    this._cameraLayer.addMarker(ccamera.marker);

				ccamera.marker.camera = ccamera;
				
				ccamera.marker.events.register("mouseover",ccamera.marker,function(evt){	
						$("#map").css("cursor","pointer");
						 
				});
				ccamera.marker.events.register("mouseout",ccamera.marker,function(evt){	
						$("#map").css("cursor","default");
						OpenLayers.Event.stop(evt);
						 
				});
				this._fovLayer.addFeatures([ccamera.fov]);	
			}
			
			//加入轨迹
			if(this._cameras[0].states.length > 1){
				ccamera.calcTrack();
				this._trackLayer.addFeatures(ccamera.track);
			}	
		};
		this.CameraCollection.attach(map);
	},
	checkStateTimes:function(){
		
	},
	checkStateTime:function(i){
		var stateObj = new Object();
		var duration = (this.endTime - this.startTime)/1000.0;
		
		if(i == 0){
			stateObj.startMinTime = 0;
		}else{
			stateObj.startMinTime = this.camera.states[i-1].endTime;
		}
		
		if(i == this.camera.states.length-1){
			stateObj.endMaxTime = duration/1000.0;
		}else{
			stateObj.endMinTime = this.camera.states[i+1].startTime;
		}
		
		return stateObj;	
	},
	addCameraState:function(cameraState){
		this.camera.states.push(cameraState);
		this.update();
	},
	removeCameraState:function(idx){
		this.camera.states.splice(idx,1);
		this.update();
	}
 });
 
OpenLayers.VideoList = OpenLayers.Class(OpenLayers.CameraList,{
	defaultStyle:function(i){
		var videos = this.cameras;
		var style= $("<div id="+i+" class=\"item\" style=\"width:100%;height:80px;cursor:pointer\">"
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
				+"			</table>"
				+"		</td>"					
				+"	</tr>"
				+"</table>"
			+"</div>");
		return style;
	}
});
OpenLayers.VideoQueryer = OpenLayers.Class(OpenLayers.CameraQueryer,
   {
   	queryurl:"videoQuery.php",
   	MODE:{
   		NONE:"none",
		POINT:"point",
		LINE:"line",
		RECT:"rect",
		POLYGON:"polygon"
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
			point:new OpenLayers.Control.DrawFeature(selectLayer,
                        OpenLayers.Handler.Point),
            line:new OpenLayers.Control.DrawFeature(selectLayer,
           	            OpenLayers.Handler.Path),
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
				queryer.query("","","");
			});
        }
	
		this.drawControls = drawControls;
	},
	query:function(queryString,startTime,endTime){
		var selectLayer = this.selectLayer;
		var features = selectLayer.features;
		var GeoString = "";
		if(features.length > 0){
			var geojson = new OpenLayers.Format.GeoJSON();
			GeoString = geojson.write(selectLayer.features[0].geometry);
		}
								
		var searchJson = {"type":"Feature",
					"geometry":(!GeoString) ? "" : eval("("+GeoString+")"),
					"properties":{
						"searchString":queryString,
						"startTime":startTime,
						"endTime":endTime,
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
				    //$("#error").html(data);					
					var videos = OpenLayers.Video.Prase(data);
					mediante.setCameras(videos);
				},
				error:function(){
					alert("请求异常！");
				}
		});
	}
});
/*
 * VideoViewer
 *视频展示集合
 *  */
OpenLayers.VideoViewer = OpenLayers.Class(OpenLayers.CameraViewer,{
	select:function(camera,isScrollTo){
		this.cameraCollection.play(camera);
		this.cameraList.select(camera,isScrollTo);
	}
});
/**
 * OpenLayers.RDrag 
 * 拖拽控件
 */
OpenLayers.RDrag = OpenLayers.Class({
	o: null,       
    initialize: function (o) {
       o.onmousedown = this.start;
       o.rDrag = this;
       o.style.left = '500px';
       o.style.top = '200px';
     },
    start: function (e) {
       var o;
       var rDrag = this.rDrag;
       e = rDrag.fixEvent(e);
       //e.preventDefault && e.preventDefault();
       rDrag.o = o = this;
       o.x = e.clientX - rDrag.o.offsetLeft;
       o.y = e.clientY - rDrag.o.offsetTop;
       o.onmousemove = rDrag.move;
       o.onmouseup = rDrag.end;
     },
     move: function (e) {
     	   var rDrag = this.rDrag;
           e = rDrag.fixEvent(e);
           var oLeft, oTop;
           oLeft = e.clientX - rDrag.o.x;
           oTop = e.clientY - rDrag.o.y;
           this.rDrag.o.style.left = oLeft + 'px';
           this.rDrag.o.style.top = oTop + 'px';
     },
     end: function (e) {
           e = this.rDrag.fixEvent(e);
           var o = this.rDrag.o;
           this.rDrag.o = o.onmousemove = o.onmouseup = null;
     },
    fixEvent: function (e) {
           if (!e) {
               e = window.event;
               e.target = e.srcElement;
               e.layerX = e.offsetX;
               e.layerY = e.offsetY;
           }
           return e;
    }
});