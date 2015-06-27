//执行引用
(function (){
	var layerFile = [
		"tian.js"
	];
	//获取当前引用
	var scripts = document.getElementsByTagName("script");
    var lastScript = scripts[scripts.length-1];
	var src = lastScript.src;
	var index = src.lastIndexOf("/");
	src = src.substring(0,index+1);
 
	for(var i=0;i< layerFile.length;i++){
		document.write("<script src="+src+layerFile+"></script>");
	}
})();
/**
统一地图配置
MapConfig
**/
/*
var MapConfig = {
	createMap:function(name){
		var map = new OpenLayers.Map(name);
		var layer = new OpenLayers.Layer.TianWLayer("矢量地图","vec_w");
		var cvalayer = new OpenLayers.Layer.TianWLayer("标注","cva_w");
		cvalayer.isBaseLayer= false;
		map.addLayers([cvalayer,layer]);
		map.setCenter(new OpenLayers.LonLat(13237081.853,3778646.925), 17);
		map.addControl(new OpenLayers.Control.MousePosition());
		return map;
	},
	transForm:function(pts){
		var ptsArray = new Array();		
		return pts;
	}
};
*/
var MapConfig = {
	createMap:function(name){
		var map = new OpenLayers.Map(name);
		var layer = new OpenLayers.Layer.TiandituLayer("矢量地图","vec_c");
		var cvalayer = new OpenLayers.Layer.TiandituLayer("标注","cva_c");
		cvalayer.isBaseLayer= false;
		map.addLayers([cvalayer,layer]);
		map.setCenter(new OpenLayers.LonLat(118.91040,32.11651), 17);
		map.addControl(new OpenLayers.Control.MousePosition());
		return map;
	},
	transform:function(geomerty){
		return geomerty.clone().transform('EPSG:900913','EPSG:4326');
	},
	rTransform:function(geomerty){
		return geomerty.clone().transform('EPSG:4326','EPSG:900913');
	}
};
