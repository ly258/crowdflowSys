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
	peopleChart:null,
	queryurl:"peopleQuery.php",

	initialize : function(peopleList,peopleChart){
		this.peopleList = peopleList;
		this.peopleChart = peopleChart;

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
				queryer.peopleChart.selectGeometry = MapConfig.rTransform(e.feature.geometry);
				queryer.peopleChart.totalPeopleCountMode = "area";
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
	mediante:null,//中介者
	presentpeonumContainerName:null,

	initialize:function(name,peopleChart,presentpeonumContainerName){
		this.container = $("#"+name);
		this.peopleChart = peopleChart;
		this.presentpeonumContainerName = presentpeonumContainerName;
		//this.defaultStyle();
	},
	
	defaultStyle : function(i){
		var cameras = this.cameras;
		var select_curveshow;//控制摄像机列表中是否显示曲线图
		var mediante = this.mediante;

		//各摄像机下人群数量初始化
		var peonum = (cameras[i].number==-1) ? "未做统计" : cameras[i].number;
		
		if(cameras[i].number==-1)
		{
			select_curveshow = "disabled='disabled'";
		}else
		{
			select_curveshow = "";
			//有人数统计的摄像机入循环队列
			mediante.peopleChart.queue.enQueue(i);
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
				+"                  	<select onClick='stopPro()' id='select"+i+"' "+select_curveshow+">"
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
		var mediante = this.mediante;
		
		container.empty();
		for(var i = 0;i < cameras.length;i++){
			
			var item = this.defaultStyle(i);
			cameras[i].index = i;
			container.append(item);			
		}

		//定义点击函数
		$(".item").click(function(){		
			mediante.select(cameras[$(this).attr("id")],true);			
		});
		//列表变色处理
 		$(".item").mouseover(function(){
				$(this).css("background-color","yellow");		 
		});

		$(".item").mouseout(function(){
				$(this).css("background-color","white");		  
	    });
	    //初始化是否显示折线图
	    for (var i = 0; i < 4; i++) {
			$("#select"+mediante.peopleChart.queue.base[i]).val("1");
		};	
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
	mediante:null,//中介者

	initialize : function(cameras){
		this._cameras = cameras;
		this._fovLayer = new OpenLayers.Layer.Vector("FOV");
		this._trackLayer = new OpenLayers.Layer.Vector("轨迹");
		this._cameraLayer = new OpenLayers.Layer.Markers("摄像头");
	},
	
	renderCamera : function(camera){
		var mediante = this.mediante;
		this._cameraLayer.addMarker(camera.marker);

		camera.marker.camera = camera;		
		camera.marker.events.register("click",camera.marker,function(evt){	
				mediante.select(this.camera,true);  
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

//人群时空分布
OpenLayers.peopleDistribution = OpenLayers.Class({
	map:null,
	cameras:null,
	peopleDistributionLayer:null,
	initialize : function(){
		var peopleDistributionLayer = new OpenLayers.Layer.Vector("时空分布",{
			styleMap : new OpenLayers.StyleMap({'default':{
				strokeColor: "#00CC00",
                strokeOpacity: 1,
                strokeWidth: 1,
                fillColor: "#00CC00",
                fillOpacity: 0.5,
                pointRadius: 2,
                pointerEvents: "visiblePainted",
			}})
		});
		this.peopleDistributionLayer = peopleDistributionLayer;
	},
	update : function(){
		this.peopleDistributionLayer.removeAllFeatures();
		var cameras = this.cameras;
		var pointcollection = new Array();
		for(var i=0;i<cameras.length;i++)
			for(var j=0;j<cameras[i].peoplePosition.length;j++)
			{
				if(this.cameras[i].peoplePosition[j].y!=-1)
				{
					var point = new OpenLayers.Geometry.Point(this.cameras[i].peoplePosition[j].x,this.cameras[i].peoplePosition[j].y);
					var pointFeature = new OpenLayers.Feature.Vector(MapConfig.transform(point));
					pointcollection.push(pointFeature);
				}				
			}	
			this.peopleDistributionLayer.addFeatures(pointcollection);	
	},
	attach : function(map){
		map.addLayers([this.peopleDistributionLayer]);
		this.map = map;
	},
	detach : function(){
		if(!this.map) return;

		this.map.removeLayer(this.peopleDistributionLayer);
		this.map = null;
	},
});

//人群运动趋势
OpenLayers.peopleDirection = OpenLayers.Class({
	map:null,
	cameras:null,
	peopleDirectionLayer:null,
	initialize : function(){
		var peopleDirectionLayer = new OpenLayers.Layer.Vector("运动趋势",{
			styleMap : new OpenLayers.StyleMap({'default':{
				strokeColor: "#00FF00", 
				strokeWidth: 2, 
				strokeDashstyle: "solid", 
				strokeLinecap: "square"
			}})
		});
		this.peopleDirectionLayer = peopleDirectionLayer;
	},
	update : function(){
		this.peopleDirectionLayer.removeAllFeatures();
		var cameras = this.cameras;
		var linecollection = new Array();
		var polygoncolllection = new Array();
		var ptsArr = new Array();
		var pointlist = [];
		var style_polygon = {
		                strokeColor: "#00FF00",
		                strokeWidth: 2,
		                strokeOpacity: 0.8,
		                fillOpacity: 0.8,
		                fillColor: "#00FF00",
		        };
		for(var i=0;i<cameras.length;i++)
			for(var j=0;j<cameras[i].startpoint.length;j++)
			{
				var st = new OpenLayers.Geometry.Point(this.cameras[i].startpoint[j].x,this.cameras[i].startpoint[j].y); 
		    	var end = new OpenLayers.Geometry.Point(this.cameras[i].endpoint[j].x,this.cameras[i].endpoint[j].y);
		    	var angle;//计算旋转角度
				var temp = Math.sqrt((end.y-st.y)*(end.y-st.y)+(end.x-st.x)*(end.x-st.x));
				if((end.y-st.y)>0&&(end.x-st.x)>0)//第一象限
					angle = Math.asin((end.x-st.x)/temp);
				else if((end.y-st.y)>0&&(end.x-st.x)<0)//第四象限
					angle = 2*Math.PI+Math.asin((end.x-st.x)/temp);
				else if((end.y-st.y)<0&&(end.x-st.x)>0)//第二象限
					angle = Math.acos((end.y-st.y)/temp);
				else if((end.y-st.y)<0&&(end.x-st.x)<0)//第三象限
					angle = Math.PI-Math.asin((end.x-st.x)/temp);

				var end02 = new OpenLayers.Geometry.Point(0*Math.cos(angle)-10*Math.sin(-angle)+st.x,0*Math.sin(-angle)+10*Math.cos(-angle)+st.y);
				pointlist.splice(0,pointlist.length);
				pointlist.push(MapConfig.transform(st));
				pointlist.push(MapConfig.transform(end02));

				var lineFeature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.LineString(pointlist),null,null);
				linecollection.push(lineFeature);

				ptsArr.splice(0,ptsArr.length);
				ptsArr.push(MapConfig.transform(new OpenLayers.Geometry.Point(0*Math.cos(angle)-10*Math.sin(-angle)+st.x,0*Math.sin(-angle)+10*Math.cos(-angle)+st.y)));
				ptsArr.push(MapConfig.transform(new OpenLayers.Geometry.Point(-2*Math.cos(angle)-8*Math.sin(-angle)+st.x,-2*Math.sin(-angle)+8*Math.cos(-angle)+st.y)));
				ptsArr.push(MapConfig.transform(new OpenLayers.Geometry.Point(2*Math.cos(angle)-8*Math.sin(-angle)+st.x,2*Math.sin(-angle)+8*Math.cos(-angle)+st.y)));

				var linearRing = new OpenLayers.Geometry.LinearRing(ptsArr);
				var polygon = new OpenLayers.Geometry.Polygon([linearRing]);
		        
		        polygonFeature = new OpenLayers.Feature.Vector(polygon,null,style_polygon);
		        polygoncolllection.push(polygonFeature);
		        //this.peopleDirectionLayer.addFeatures([polygonFeature]);			
			}	
			this.peopleDirectionLayer.addFeatures(linecollection);
			this.peopleDirectionLayer.addFeatures(polygoncolllection);	

	},
	attach : function(map){
		map.addLayers([this.peopleDirectionLayer]);
		this.map = map;
	},
	detach : function(){
		if(!this.map) return;

		this.map.removeLayer(this.peopleDirectionLayer);
		this.map = null;
	},
});

//一个摄像机展示总集合
OpenLayers.peopleViewer = OpenLayers.Class({
	map:null,
	peopleList:null,
	peopleChart:null,
	peopleCollection:null,
	peopleDistribution:null,
	peopleDirection:null,
	videoPlayer:null,
	cameras:null,
	analysisMode:null,

	initialize:function(peopleList,peopleChart,peopleCollection,peopleDistribution,peopleDirection,videoPlayer){
		this.peopleList = peopleList;
		this.peopleChart = peopleChart;
		this.peopleCollection = peopleCollection;
		this.peopleDistribution = peopleDistribution;
		this.peopleDirection = peopleDirection;
		this.videoPlayer = videoPlayer;
		peopleCollection.mediante = this;
		peopleList.mediante = this;
	},
	setCameras:function(cameras){
		this.peopleList.cameras = cameras;
		this.peopleList.updateList();
	},
	setAnalysisMode:function(analysisMode){
		this.analysisMode = analysisMode;
	},
	select:function(camera,isScrollTo){
		this.videoPlayer.play(camera.avpath);
		this.peopleList.select(camera,isScrollTo);
	},
	attach:function(map){
		this.map = map;
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
						if(peopleviewer.peopleDistribution.map!=null)
						{
							peopleviewer.peopleDistribution.detach();
						}
						if(peopleviewer.peopleDirection.map!=null)
						{
							peopleviewer.peopleDirection.detach();
						}						
						peopleviewer.peopleCollection.setCameras(cameras);
						peopleviewer.peopleCollection.update();
						break;
					case 2:
						if(peopleviewer.peopleDistribution.map==null)
						{
							peopleviewer.peopleDistribution.attach(peopleviewer.map);
						}
						if(peopleviewer.peopleDirection.map!=null)
						{
							peopleviewer.peopleDirection.detach();
						}
						peopleviewer.peopleCollection.setCameras(cameras);
						peopleviewer.peopleCollection.update();
						peopleviewer.peopleDistribution.cameras = cameras;
						peopleviewer.peopleDistribution.update();
						break;
					case 3:
						if(peopleviewer.peopleDirection.map==null)
						{
							peopleviewer.peopleDirection.attach(peopleviewer.map);
						}
						if(peopleviewer.peopleDistribution.map!=null)
						{
							peopleviewer.peopleDistribution.detach();
						}
						peopleviewer.peopleCollection.setCameras(cameras);
						peopleviewer.peopleCollection.update();
						peopleviewer.peopleDirection.cameras = cameras;
						peopleviewer.peopleDirection.update();
						break;
				}
			}
		});
	},
	detach:function(){
		this.cameraList.container.empty();
	}
});