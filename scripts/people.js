/********************************************************************************************************
people.js
基于openlayers的人群库
create by liuyang
2015/01/09
*********************************************************************************************************/
//
//人群查询分析器
OpenLayers.PeopleQueryer = OpenLayers.Class({
	peopleList:null,
	peopleCollection:null,
	queryurl:"peopleQuery.php",

	initialize : function(peopleList,peopleCollection){
		this.peopleList = peopleList;
		this.peopleCollection = peopleCollection;
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
	Dynacharts:null,
	peopleCollection:null,
	peopleDistribution:null,
	map:null,
	analysisMode:null,
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

	linkage : function(){
		var that = this;
		$.ajax({
			type:"POST",
			url:"peopleQuery.php",
			dataType:"json",
			success:function(data){
				var cameras = OpenLayers.People.Prase(data);
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
				};				
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