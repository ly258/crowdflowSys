<?php
/**************************
query解析类
class QueryFilter
**/
class CameraQueryFilter{
  private $properties;
  private $geometry;
  public function __construct(){
  
  }
  /************************************
  从Json中解析出QueryFilter
  static fromGeoJson($json)
  ******************************/
  public static function fromGeoJson($json){
	$queryFilter = new CameraQueryFilter();
	$queryFilter->properties= isset($json["properties"]) ?  $json["properties"] : "";
	$queryFilter->geometry = isset($json["geometry"]["coordinates"])
	                          ? $json["geometry"]["coordinates"] : "";
	return $queryFilter;
  }
  /*****************************************
  解析空间查询
  public function LocationString($location)
  *******************************/
  public function locationString($location){
    if(empty($this->geometry)
	  ||empty($location)){
		return "";
	}
	$geoString = "ST_GeomFromText('POLYGON((";
	$idx = false;
    foreach($this->geometry[0] as $value){
		$tmpGeoJson = $value[0]." ".$value[1];
		//若第一次
		if(!$idx){
			$idx = true;
		}else{
			$tmpGeoJson = ",".$tmpGeoJson;
		}
		$geoString .= $tmpGeoJson;
	}
	$geoString.="))')";
	return "ST_Contains(".$geoString.",".$location.")";
  }
/*****************************************
  解析属性查询
  public function LocationString($location)
  *******************************/
  public function whereString($where){
	if(empty($where)
	   ||empty($this->properties)){
		return "";
	}
	$whereStr = "(";
	$idx = false;
	foreach($where as $value){
		$tmpwhere = $value." like '%".$this->properties["searchString"]."%'";
		//若第一次
		if(!$idx){
			$idx = true;
		}else{
			$tmpwhere = " OR ".$tmpwhere;
		}
		$whereStr.=$tmpwhere;
	}
	$whereStr.=")";
	
	return $whereStr;
  }
}
/*******************************************
搜索Camera
function searchCamera($link,$queryFilter)
**/
 function searchCamera($link,$queryFilter){
	$geom = $queryFilter->locationString('location');
	$pwhere = $queryFilter->whereString(array("videocms_camera.id","name","const_org","use_org"));
	//
	$where = "";
	if(empty($geom) && empty($pwhere)){
		$where = "";
	}else if(empty($geom)){
		$where = "WHERE ".$pwhere;
	}else if(empty($pwhere)){
		$where = "WHERE ".$geom;
	}else{
		$where = "WHERE ".$geom." AND ".$pwhere;
	}
	
	$sql = <<<EOF
	       SELECT videocms_camera.id as id,name,pvgip,avpath,type,state,ST_X(location) as x,ST_Y(location) as y,height,
           ccd_width,ccd_height,pan,tilt,focal,max_focal,min_focal,max_length,
		   const_org,use_org,const_time,max_tilt,min_tilt,max_rota,min_rota	   
		   FROM videocms_camera LEFT OUTER JOIN videocms_cameraadjust
	       ON videocms_camera.id = videocms_cameraadjust.id {$where}
EOF;
	//echo $sql;
	$query = query($link,$sql);
	$json="{\"type\":\"FeatureCollection\",\"features\":[";
	$idx = false;
	while($rst = fetchArray($query)){
		//处理type
		if($rst['type']== 0){
			$rst['max_tilt'] =  $rst['min_tilt'] = $rst['tilt'];
			$rst['max_rota'] =  $rst['min_rota'] = $rst['pan'];
		}else if($rst['type']== 0){
			$rst['max_rota'] = 360;
			$rst['min_rota'] = 0;
		}
		
		$tmpGeoJson = <<<EOF
		{"type":"Feature",
		 "geometry":{
			 "type":"Point",
			 "coordinates":[{$rst['x']},{$rst['y']}]
		 },
		 "properties":{
			 "id":"{$rst['id']}",
			 "name":"{$rst['name']}",
			 "ip":"{$rst['pvgip']}",
			 "avpath":"{$rst['avpath']}",
			 "type":{$rst['type']},
			 "state":{$rst['state']},
			 "height":"{$rst['height']}",
			 "pan":"{$rst['pan']}",
			 "minpan":"{$rst['min_rota']}",
			 "maxpan":"{$rst['max_rota']}",
			 "tilt":"{$rst['tilt']}",
			 "mintilt":"{$rst['max_tilt']}",
			 "maxtilt":"{$rst['min_tilt']}",
			 "focal":"{$rst['focal']}",
			 "minfocal":"{$rst['max_focal']}",
			 "maxfocal":"{$rst['min_focal']}",
			 "ccdwidth":"{$rst['ccd_width']}",
			 "ccdheight":"{$rst['ccd_height']}",
			 "maxdist":"{$rst['max_length']}",
			 "constorg":"{$rst['const_org']}",
			 "useorg":"{$rst['use_org']}",
			 "consttime":"{$rst['const_time']}"
		 }
	}
EOF;
	//若第一次
	if(!$idx){
	    $idx = true;
	}else{
		$tmpGeoJson=",".$tmpGeoJson;
	}
	
	$json.=$tmpGeoJson;
	}
	$json.="]}";
	//echo $json;
	return $json;
 }