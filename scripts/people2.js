/********************************************************************************************************
people.js
基于openlayers的人群相关库
create by liuyang
2015/01/09
*********************************************************************************************************/
//
//人群查询分析器
OpenLayers.PeopleQueryer = OpenLayers.Class({
	map:null,
	selectLayer:null,
	drawControls:null,
	MODE:{
		POINT:"point",
		RECT:"rect",
		POLYGON:"POLYGON"
	},
	peopleList:null,
	queryurl:"peopleQuery.php",

	initialize : function(peopleList){
		this.peopleList = peopleList;

		//定义选择图层样式
		var selectLayer = new OpenLayers.Layer.Vector("selectLayer",{
			styleMap:new OpenLayers.StyleMap({'default':{
				strokeColor: "#00FFFF",//边框颜色
                strokeOpacity: 1,//边框透明度
                strokeWidth: 3,//边框宽度
                fillColor: "#00FFFF",//多边形填充色
                fillOpacity: 0.1,//多边形填充透明度
			}})
		});
		this.selectLayer = selectLayer;
		var drawControls = {
			//绘制矩形
			rect:new OpenLayers.Control.DrawFeature(selectLayer,
                        OpenLayers.Handler.RegularPolygon, {
                        handlerOptions: {
                                sides: 4,
                                irregular: true
           }}),
			//绘制多边形
			polygon:new OpenLayers.Control.DrawFeature(selectLayer,
                          OpenLayers.Handler.Polygon)
		};
		this.drawControls = drawControls;
		var queryer = this;
		for(var key in drawControls) {
			drawControls[key].events.register("featureadded",drawControls[key],function (e){
				queryer.selectLayer.removeAllFeatures();
				queryer.selectLayer.addFeatures([e.feature]);
				peopleList.selectGeometry = MapConfig.rTransform(e.feature.geometry);
			});
        }
	},
	query : function(){
		//系统初始化摄像机列表
		var peopleList = this.peopleList;
		$.ajax({
			type:"POST",
			async:true,
			url:this.queryurl,
			dataType:"json",
			success:function(data){
				var cameras = OpenLayers.People.Prase(data);
				peopleList.cameras = cameras;
				peopleList.updateList();
			}
		})
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
	attach:function(map){
		map.addLayers([this.selectLayer]);
		for(var key in this.drawControls)
		{
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
//人群类
OpenLayers.People = OpenLayers.Class(OpenLayers.Camera,{
	number:null,
	peoplePosition:null
});

//摄像机解析GeoJson解析
OpenLayers.People.Prase=function(cGeoJson){
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
			camera.number = featureCollection[i].data.num;
			camera.peoplePosition = featureCollection[i].data.people;
			camera.alarm = featureCollection[i].data.alarm;
			camera.startpoint = featureCollection[i].data.startpoint;
			camera.endpoint = featureCollection[i].data.endpoint;
            camera.index = i;
			cameras.push(camera);
		}
		return cameras;
};

//摄像机列表
OpenLayers.peopleList = OpenLayers.Class(OpenLayers.CameraList,{
	peopleChart:null,//人群数量变化折线图
	presentpeonumContainerName:null,

	initialize:function(name,peopleChart,presentpeonumContainerName){
		this.container = $("#"+name);
		this.peopleChart = peopleChart;
		this.presentpeonumContainerName = presentpeonumContainerName;
		//this.defaultStyle();
	},
	
	defaultStyle : function(i){
		var cameras = this.cameras;
		var typeName,stateName;//摄像机列表中摄像机类型与摄像机状态
		var select_curveshow;//控制摄像机列表中是否显示曲线图

		//判断
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
		//各摄像机下人群数量初始化
		var peonum = (cameras[i].number==-1) ? "未做统计" : cameras[i].number;
		
		if(cameras[i].number==-1)
		{
			select_curveshow = "disabled='disabled'";
		}else
		{
			select_curveshow = "";
			//有人数统计的摄像机入循环队列
			this.peopleChart.queue.enQueue(i);
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
				+"				 	<th id='presentpeonum"+i+"' colspan=\"1\">当前人数:"+peonum+"</th>"
				+"                  <th colspan=\"1\">&nbsp&nbsp&nbsp统计曲线：</th>"
				+"                  <th colspan=\"1\">"
				+"                  	<select onChange='selectchangeable("+i+")' id='select"+i+"' "+select_curveshow+">"
				+"                      	<option value='1'>是</option>"
				+"                          <option value='0' selected='selected'>否</option>"
				+"                      </select>"
				+"                  </th>"
				+"				</tr>"
				+"			</table>"
				+"		</td>"
				+"		<td align=\"center\">"
				+"			<img src=\"./images/"+(Number(cameras[i].type)*5+Number(cameras[i].tstate))+".png\" width=\"50\" height=\"40\"/>"
				+"		</td>"						
				+"	</tr>"
				+"</table>"
			+"</div>");
		return style;
	},

	updateList:function(){	
		var container = this.container;
		var cameras = this.cameras;
		//var cameraCollection = this.cameraCollection;
		
		container.empty();
		for(var i = 0;i < cameras.length;i++){
			
			var item = this.defaultStyle(i);
			cameras[i].index = i;
			container.append(item);			
		}

		var peolist = this;
		//定义点击函数
		$(".item").click(function(){		
			peolist.realtimeVideoSelect(cameras[$(this).attr("id")],true);			
		});
		//列表变色处理
 		$(".item").mouseover(function(){
				$(this).css("background-color","yellow");		 
		});

		$(".item").mouseout(function(){
				$(this).css("background-color","white");		  
	    });	
	},

	update : function(cameras){				
		for (var i = 0; i < cameras.length; i++) {
			if(cameras[i].number==-1)
			{
				$("#"+this.presentpeonumContainerName+i).text("当前人数：未做统计");
			}else{
				if(cameras[i].number>cameras[i].alarm)
				{
					$("#"+this.presentpeonumContainerName+i).html("<b style='color:red;'>当前人数："+cameras[i].number+"</b>");
				}
				else
				{
					$("#"+this.presentpeonumContainerName+i).html("<b>当前人数："+cameras[i].number+"</b>");
				}
			}
		}	
	},
});

//摄像机人群数量统计折线图
OpenLayers.peopleChart = OpenLayers.Class({
	container:null,
	queue:null,
	totalNumDynachart:null,
	totalPeopleCountMode:"whole",
	totalAlarm:100,
	tempNum:0,
	areaPeoNum:0,
	selectGeometry:null,
	Dynacharts:null,

	initialize:function(name){
		this.container = name;
		if(this.queue == null)
			this.queue = new CycleQueue();
	},

	updateQueue : function(data){
		if(this.queue == null)
			this.queue = new CycleQueue();
		this.queue.enQueue(data);
		return this.queue;
	},

	count : function(data){
		for (var i=0;i<data.peoplePosition.length;i++) {
			var peoplePos = new OpenLayers.Geometry.Point(data.peoplePosition[i].x,data.peoplePosition[i].y);
			if(this.selectGeometry.intersects(peoplePos))
				this.tempNum++;
		};
	},

	update:function(cameras){
		this.areaPeoNum = 0;

		for(var i=0; i < cameras.length; i++){
			if(this.totalPeopleCountMode=="area")
				{
					this.count(cameras[i]);	
				}else{
					if(cameras[i].number!=-1)
					{
						this.areaPeoNum = this.areaPeoNum+cameras[i].number;
					}						
				}
		}
		if(this.totalPeopleCountMode=="area")
		{
			this.areaPeoNum = this.tempNum;	
			this.tempNum = 0;
		}
		this.totalNumDynachart.addTotalNumPoint(this.areaPeoNum,this.totalAlarm);
		for (var i = 0; i < 4; i++) {
			$("#" + this.container + (i+1)).html(cameras[this.queue.base[i]].name);
			this.Dynacharts[i].addPoint(cameras[this.queue.base[i]]);
		};
	},
});

//实时视频播放器
OpenLayers.videoPlayer = OpenLayers.Class({
	vlcPlayer:null,
	targetURL:"",

	initialize : function(videoPlayer){
		this.vlcPlayer = videoPlayer;
	},
	play : function(url){
		$("#videoPlayer").show();
		this.doGo(url);
	},
	doGo : function(targetURL)
	{
		this.vlcPlayer.playlist.stop();
	    if( this.vlcPlayer )
	    {
	        this.vlcPlayer.playlist.items.clear();
	        while( this.vlcPlayer.playlist.items.count > 0 )
	        {
	            // clear() may return before the playlist has actually been cleared
	            // just wait for it to finish its job
	        }
	        var options = [":rtsp-tcp"];
	        var itemId = this.vlcPlayer.playlist.add(targetURL,"",options);
	        options = [];
	        if( itemId != -1 )
	        {
	            // play MRL
	            this.vlcPlayer.playlist.playItem(itemId);
	        }
	        else
	        {
	            alert("cannot play at the moment !");
	        }
	    }
	},
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

//摄像机点位图&实时监控
OpenLayers.peopleCollection = OpenLayers.Class(OpenLayers.CameraCollection,{	
	videoPlayer:null,

	initialize : function(cameras,videoPlayer){
		this._cameras = cameras;
		this._fovLayer = new OpenLayers.Layer.Vector("FOV");
		this._trackLayer = new OpenLayers.Layer.Vector("轨迹");
		this._cameraLayer = new OpenLayers.Layer.Markers("摄像头");
		this.videoPlayer = videoPlayer;
	},


	realtimeVideoSelect : function(camera,isScrollTo){
		this.videoPlayer.play(camera.avpath);
		this.select(camera,isScrollTo);
	},
	
	renderCamera : function(camera){
		var peopleCollection = this;
		this._cameraLayer.addMarker(camera.marker);

		camera.marker.camera = camera;		
		camera.marker.events.register("click",camera.marker,function(evt){	
				peopleCollection.realtimeVideoSelect(this.camera,true);  
		});
		if(camera.number>camera.alarm)
		{
			camera.fov.style = {
				strokeColor: "red",
                strokeOpacity: 1,
                strokeWidth: 1,
                fillColor: "red",
                fillOpacity: 0.5,
				// label with \n linebreaks
                label : String(camera.number),                   
                fontColor: "black",
                fontSize: "12px",
                fontFamily: "Courier New, monospace",
                fontWeight: "bold",
                labelAlign: "cm",
                labelXOffset: "0",
                labelYOffset: "0",
                labelOutlineColor: "white",
                labelOutlineWidth: 3
			}
		}else{
			camera.fov.style = {
				strokeColor: "#7CB5EC",
                strokeOpacity: 1,
                strokeWidth: 1,
                fillColor: "#7CB5EC",
                fillOpacity: 0.5,
				// label with \n linebreaks
                label : String(camera.number),                   
                fontColor: "black",
                fontSize: "12px",
                fontFamily: "Courier New, monospace",
                fontWeight: "bold",
                labelAlign: "cm",
                labelXOffset: "0",
                labelYOffset: "0",
                labelOutlineColor: "white",
                labelOutlineWidth: 3
			}
		}
		this._fovLayer.addFeatures([camera.fov]);
	},
});

//一个摄像机展示总集合
OpenLayers.peopleViewer = OpenLayers.Class({
	peopleList:null,
	peopleChart:null,
	peopleCollection:null,
	cameras:null,
	analysisMode:null,

	initialize:function(peopleList,peopleChart,peopleCollection){
		this.peopleList = peopleList;
		this.peopleChart = peopleChart;
		this.peopleCollection = peopleCollection;
	},
	setCameras:function(cameras){
		this.peopleList.cameras = cameras;
		this.peopleList.updateList();
	},
	setAnalysisMode:function(analysisMode){
		this.analysisMode = analysisMode;
	},
	select:function(camera,isScrollTo){
		this.peopleList.select(camera,isScrollTo);
	},
	attach:function(map){
		this.peopleList.updateList();
	},
	notify:function(){
		var peopleviewer = this;
		$.ajax({
			type:"POST",
			url:"peopleQuery.php",
			dataType:"json",
			success:function(data){
				var cameras = OpenLayers.People.Prase(data);
				peopleviewer.peopleList.update(cameras);
				peopleviewer.peopleChart.update(cameras);
				switch (peopleviewer.analysisMode)
				{
					case 1:						
						peopleviewer.peopleCollection.setCameras(cameras);
						peopleviewer.peopleCollection.update();
						break;
				}
			}
		});
	},
	detach:function(){
		this.cameraList.container.empty();
	}
});