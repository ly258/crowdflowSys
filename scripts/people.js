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
	peopleCollection:null,
	queryurl:"peopleQuery.php",

	initialize : function(peopleList,peopleCollection){
		this.peopleList = peopleList;
		this.peopleCollection = peopleCollection;

		var selectLayer = new OpenLayers.Layer.Vector("selectLayer",{
			styleMap:new OpenLayers.StyleMap({'default':{
				strokeColor: "#00FFFF",
                    strokeOpacity: 1,
                    strokeWidth: 3,
                    fillColor: "#00FFFF",
                    fillOpacity: 0.1,
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
		this.drawControls = drawControls;
		var queryer = this;
		for(var key in drawControls) {
			drawControls[key].events.register("featureadded",drawControls[key],function (e){
				queryer.selectLayer.removeAllFeatures();
				queryer.selectLayer.addFeatures([e.feature]);
				peopleList.selectGeometry = MapConfig.rTransform(e.feature.geometry);
				peopleList.totalPeopleCountMode = "area";
			});
        }
	},
	query : function(){
		var peopleList = this.peopleList;
		var peopleCollection = this.peopleCollection;
		$.ajax({
			type:"POST",
			async:false,
			url:this.queryurl,
			dataType:"json",
			success:function(data){
				var cameras = OpenLayers.People.Prase(data);
				peopleList.cameras = cameras;
				peopleCollection.setCameras(cameras);
				peopleList.update();
				peopleCollection.update();
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

//摄像机解析
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
			cameras.push(camera);
		}
		return cameras;
};

OpenLayers.peopleList = OpenLayers.Class(OpenLayers.CameraList,{
	queue:null,
	totalNumDynachart:null,
	Dynacharts:null,
	peopleCollection:null,
	peopleDistribution:null,
	map:null,
	analysisMode:null,
	selectGeometry:null,
	tempNum:0,
	areaPeoNum:0,
	totalPeopleCountMode:"whole",
	totalAlarm:100,
	defaultStyle : function(i){
		var cameras = this.cameras;
		var typeName,stateName;
		var select_curveshow;
		if(this.queue == null)
			this.queue = new CycleQueue();

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

		var peonum = (cameras[i].number==-1) ? "未做统计" : cameras[i].number;
		
		if(cameras[i].number==-1)
		{
			select_curveshow = "disabled='disabled'";
		}else
		{
			select_curveshow = "";
			this.queue.enQueue(i);
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

	realtimeVideoSelect : function(camera,isScrollTo){
		this.peopleCollection.play();
		this.select(camera,isScrollTo);
	},

	linkage : function(){
		var that = this;
		$.ajax({
			type:"POST",
			url:"peopleQuery.php",
			dataType:"json",
			success:function(data){
				var cameras = OpenLayers.People.Prase(data);
				that.areaPeoNum = 0;				
				for (var i = 0; i < cameras.length; i++) {
					if(cameras[i].number==-1)
					{
						$("#presentpeonum"+i).text("当前人数：未做统计");
					}else{
						if(cameras[i].number>cameras[i].alarm)
						{
							$("#presentpeonum"+i).html("<b style='color:red;'>当前人数："+cameras[i].number+"</b>");
						}
						else
						{
							$("#presentpeonum"+i).html("<b>当前人数："+cameras[i].number+"</b>");
						}
					}
					if(that.totalPeopleCountMode=="area")
					{
						that.count(cameras[i]);	
					}else{
						if(cameras[i].number!=-1)
						{
							that.areaPeoNum = that.areaPeoNum+cameras[i].number;
						}						
					}			
				};	
				if(that.totalPeopleCountMode=="area")
				{
					that.areaPeoNum = that.tempNum;	
					that.tempNum = 0;
				}
				that.totalNumDynachart.addTotalNumPoint(that.areaPeoNum,that.totalAlarm);
				for (var i = 0; i < 4; i++) {
					$("#curvegraphheader0"+(i+1)).html(cameras[that.queue.base[i]].name);
					that.Dynacharts[i].addPoint(cameras[that.queue.base[i]]);
				};
				switch (that.analysisMode)
				{
					case 1:
						if(that.peopleDistribution.map!=null)
						{
							that.peopleDistribution.detach();
						}						
						that.peopleCollection.setCameras(cameras);
						that.peopleCollection.update();
						break;
					case 2:
						if(that.peopleDistribution.map==null)
						{
							that.peopleDistribution.attach(that.map);
						}
						that.peopleCollection.setCameras(cameras);
						that.peopleCollection.update();
						that.peopleDistribution.cameras = cameras;
						that.peopleDistribution.update();
						break;
				}			
			},
			error:function(){
				alert("请求异常！");
			}
		})
	}
});

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

OpenLayers.realmonitorDrag = OpenLayers.Class({
	oTitle:null,
	oDrag:null,
	disx:null,
	disy:null,
	initialize : function(o){
		o.onmousedown = this.fnDown;
		o.rDrag = this;
	},
	fnDown : function(event){
		event = event || window.event;
		var oTitle;
		var rDrag = this.rDrag;
		rDrag.oTitle = oTitle = this;
		rDrag.oDrag = $("#videoPlayer").get(0);
		//光标按下时光标和面板之间的距离
		rDrag.disx = event.clientX - rDrag.oDrag.offsetLeft;
		rDrag.disy = event.clientY - rDrag.oDrag.offsetTop;
		//移动
		oTitle.onmousemove = rDrag.fnMove;
		oTitle.onmouseup = rDrag.fnUp;
	},
	fnMove : function(event){
		var rDrag = this.rDrag;
		event = event || window.event;
		var l = event.clientX - this.rDrag.disx,
			t = event.clientY - this.rDrag.disy;
		this.rDrag.oDrag.style.left = l+"px";
		this.rDrag.oDrag.style.top = t+"px";
	},
	fnUp: function (e) {
           var o = this.rDrag.oTitle;
           this.rDrag.oTitle = this.rDrag.oDrag = o.onmousemove = o.onmouseup = null;
     },
});

OpenLayers.peopleCollection = OpenLayers.Class(OpenLayers.CameraCollection,{
	vlcPlayer:null,
	targetURL:"",
	peopleList:null,
	initialize : function(cameras,videoPlayer,peopleList){
		this._cameras = cameras;
		this._fovLayer = new OpenLayers.Layer.Vector("FOV");
		this._trackLayer = new OpenLayers.Layer.Vector("轨迹");
		this._cameraLayer = new OpenLayers.Layer.Markers("摄像头");
		this.vlcPlayer = videoPlayer;
		this.peopleList = peopleList;
	},
	play : function(){
		$("videoPlayer").show();
		this.doGo("rtsp://admin:admin@172.21.150.129:554/cam/realmonitor?channel=4&subtype=0");
	},
	doGo : function(targetURL)
	{
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
	renderCamera:function(camera){
		var peopleList = this.peopleList;
		this._cameraLayer.addMarker(camera.marker);
			
		camera.popup = null;
		camera.marker.camera = camera;		
		camera.marker.events.register("click",camera.marker,function(evt){	
				peopleList.select(this.camera,true);  
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