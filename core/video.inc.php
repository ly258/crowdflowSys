<?php
/** 
 VideoDB Class
 视频数据库类
 create by skx
 2014/11/25 
 **/
class VideoDB{
	private $_link; //数据库连接
	
	public function __construct($link){
		$this->connect($link);
	}
	/**
	 * 链接数据库
	 * public  function connect($_link)
    */
	public function connect($link){
		$this->_link = $link;
	}
	/**
	 * 添加视频
	 * public function remove($id)
	 */
	public function add($videoItem){
		$name = $videoItem->name;
  		$file = $videoItem->url;
		$camera = $videoItem->getRelateCamera();
  		$relate = empty($camera["id"]) ? -1 : $camera["id"];
  		$ccdwidth = $videoItem->ccdwidth;
  		$ccdheight = $videoItem->ccdheight;
  		$starttime = $videoItem->startTime;
  		$endtime = $videoItem->endTime;
		$sql = <<<EOF
    		INSERT INTO videocms_video(name,url,cid,ccd_width,ccd_height,starttime,endtime)
			VALUES('$name','$file','$relate','$ccdwidth','$ccdheight','$starttime','$endtime')
EOF;
		//echo $sql;
		if(!query($this->_link,$sql))
			return FALSE;
		
		//获取插入ID
   		$sql = "SELECT currval('vcms_video_vid_seq')";
   		$ret = fetchOne($this->_link, $sql); 
   		$vid = $ret["currval"];
		//嵌套函数
		function pt_cat($pt){
			return $pt["x"]." ".$pt["y"];	
		}
		
		$preState = null;
		foreach($videoItem->states as $value){
			//print_r($value);
			//插入状态表 
			$focal = $value->focal;
			$pan = $value->pan;
			$tilt = $value->tilt;
			$maxdist = $value->maxdist;
			$location = "ST_GeomFromText('Point(".$value->x." ".$value->y.")')";
			$height = $value->height;
			$time = $value->time+$videoItem->startTime;
			
			$coverage = join(",",array_map("pt_cat",$value->getCoverage()));
			//构造Fov
			$coverage = "ST_GeomFromText('Polygon((".$coverage."))')";
			$sql = <<<EOF
			INSERT INTO videocms_videostate(vid,focal,pan,tilt,maxdist,location,
			height,time,coverage,parent)VALUES('$vid','$focal','$pan','$tilt',
			'$maxdist',$location,'$height','$time',$coverage,'-1');
EOF;
			//echo $sql;
			if(!query($this->_link,$sql))
				return FALSE;
			
			//获取插入ID
   			$sql = "SELECT currval('vcms_videostate_vsid_seq');";
   			$ret = fetchOne($this->_link, $sql); 
   			$vsid = $ret["currval"];
			$value->id = $vsid;
			//内插状态
			if($preState != null){
				$step = 5;
				$sql = "";
				for($time = $preState->time+$step;$time < $value->time;$time +=$step){
					 $state = new VideoState();
					 $state->video = $preState->video;
					 $rate = ($value->time-$time)/($value->time-$preState->time);
					 $state->focal = $preState->focal*$rate+$value->focal*(1-$rate);
					 $state->pan = $preState->pan*$rate+$value->pan*(1-$rate);
					 $state->tilt = $preState->tilt*$rate+$value->tilt*(1-$rate);
					 $state->maxdist = $preState->maxdist*$rate+$value->maxdist*(1-$rate);
					 $state->x = $preState->x*$rate+$value->x*(1-$rate);
					 $state->y = $preState->y*$rate+$value->y*(1-$rate);
					 $location = "ST_GeomFromText('Point(".$state->x." ".$state->y.")')";
					 $state->height = $preState->height*$rate+$value->height*(1-$rate);
					 $ctime = $time+$videoItem->startTime;
					 $state->calcuCoverage();
					 $coverage = join(",",array_map("pt_cat",$state->getCoverage()));
					 //构造Fov
					 $coverage = "ST_GeomFromText('Polygon((".$coverage."))')";
					 $sql .= <<<EOF
					 INSERT INTO videocms_videostate(vid,focal,pan,tilt,maxdist,location,
				     height,time,coverage,parent)VALUES('$vid','{$state->focal}',
				     '{$state->pan}','{$state->tilt}','{$state->maxdist}',$location,
				     '{$state->height}','$ctime',$coverage,'{$preState->id}');	
EOF;
			
			 }
			if(empty($sql))
			     continue;
			//echo $sql."okkk";
			if(!query($this->_link,$sql))
				return FALSE;	
		   }
			$preState = $value;
		}
		
		return TRUE;	
	}
	/**
	 *查询视频 
	 * public function query()
	**/
	public function query($queryFilter){
		
		$whereArr = array();
		$geom = $queryFilter->spatialString('coverage');
		if(!empty($geom)){
			array_push($whereArr,$geom);
		}
		
		$time = $queryFilter->timeString();
		if(!empty($time)){
			array_push($whereArr,$time);
		}	
						  
		$whereCondition = $queryFilter->whereString();
		if(!empty($whereCondition)){
			array_push($whereArr,$whereCondition);
		}
		
		$where=join(" AND ",$whereArr);
		if(!empty($where)){
			$where = "WHERE ".$where;
		}
		//echo $whereCondition;
		$sql = <<<EOF
	       SELECT *,ST_X(location) as x,ST_Y(location) as y FROM videocms_videostate {$where} ORDER BY
	       vid ASC,time ASC
EOF;
		//echo $sql;
		$query = query($this->_link,$sql);
		$videoArray = array();
		$currentVId = -1;
		$currentIdx = -1;
		$ccdwidth = 0;
		$ccdheigh = 0;
		
		while($obj = fetchArray($query)){
			
			//建立新视频
			if($obj["vid"] != $currentVId){
				$sql = "SELECT * FROM videocms_video WHERE vid=".$obj["vid"];
				$videoObj = fetchOne($this->_link,$sql);
				$videoItem = array(
					"id"=>$videoObj["vid"],
					"name"=>$videoObj["name"],
					"url"=>$videoObj["url"],
					"relateCamera"=>$videoObj["cid"],
					"startTime"=>$videoObj["starttime"],
					"endTime"=>$videoObj["endtime"],
					"states"=>array()
				);
				$ccdwidth = $videoObj["ccd_width"];
				$ccdheight = $videoObj["ccd_height"];
				array_push($videoArray,$videoItem);
				$currentIdx++;
				$currentVId = $obj["vid"];
			}
		   //print_r($videoArray);
		   //插入State
		   $state = array("x"=>$obj["x"],"y"=>$obj["y"],
		   		"height"=>$obj["height"],"pan"=>$obj["pan"],
		   		"tilt"=>$obj["tilt"],"time"=>$obj["time"],
		   		"focal"=>$obj["focal"],"maxdist"=>$obj["maxdist"],
		   		"ccdwidth"=>$ccdwidth,
				"ccdheight"=>$ccdheight);
		   array_push($videoArray[$currentIdx]["states"],$state);
		}
		
		return $videoArray;
	}
	/**
	 *更新视频
	 * public function query()
	**/
	public function update($videoItem){
		if(empty($videoItem->id)) return false;
		
		$vid = $videoItem->id;
		$name = $videoItem->name;
  		$file = $videoItem->url;
		$camera = $videoItem->getRelateCamera();
  		$relate = empty($camera["id"]) ? -1 : $camera["id"];
  		$ccdwidth = $videoItem->ccdwidth;
  		$ccdheight = $videoItem->ccdheight;
  		$starttime = $videoItem->startTime;
  		$endtime = $videoItem->endTime;
		$sql = <<<EOF
    		UPDATE videocms_video SET name='$name',url='$file',cid='$relate',ccd_width='$ccdwidth'
    		,ccd_height='$ccdheight',starttime='$starttime',endtime='$endtime' WHERE vid=$vid
EOF;
		//echo $sql;
		query($this->_link,$sql);
		
		//删除先前状态
   		$sql = "DELETE FROM videocms_videostate WHERE vid=".$vid;
   		query($this->_link,$sql); 
   		
		//嵌套函数
		function pt_cat($pt){
			return $pt["x"]." ".$pt["y"];	
		}
		
		$preState = null;
		foreach($videoItem->states as $value){
			//print_r($value);
			//插入状态表 
			$focal = $value->focal;
			$pan = $value->pan;
			$tilt = $value->tilt;
			$maxdist = $value->maxdist;
			$location = "ST_GeomFromText('Point(".$value->x." ".$value->y.")')";
			$height = $value->height;
			$time = $value->time+$videoItem->startTime;
			
			$coverage = join(",",array_map("pt_cat",$value->getCoverage()));
			//构造Fov
			$coverage = "ST_GeomFromText('Polygon((".$coverage."))')";
			$sql = <<<EOF
			INSERT INTO videocms_videostate(vid,focal,pan,tilt,maxdist,location,
			height,time,coverage,parent)VALUES('$vid','$focal','$pan','$tilt',
			'$maxdist',$location,'$height','$time',$coverage,'-1');
EOF;
			//echo $sql;
			if(!query($this->_link,$sql))
				return FALSE;
			
			//获取插入ID
   			$sql = "SELECT currval('vcms_videostate_vsid_seq');";
   			$ret = fetchOne($this->_link, $sql); 
   			$vsid = $ret["currval"];
			$value->id = $vsid;
			//内插状态
			if($preState != null){
				$step = 5;
				$sql = "";
				for($time = $preState->time+$step;$time < $value->time;$time +=$step){
					 $state = new VideoState();
					 $state->video = $preState->video;
					 $rate = ($value->time-$time)/($value->time-$preState->time);
					 $state->focal = $preState->focal*$rate+$value->focal*(1-$rate);
					 $state->pan = $preState->pan*$rate+$value->pan*(1-$rate);
					 $state->tilt = $preState->tilt*$rate+$value->tilt*(1-$rate);
					 $state->maxdist = $preState->maxdist*$rate+$value->maxdist*(1-$rate);
					 $state->x = $preState->x*$rate+$value->x*(1-$rate);
					 $state->y = $preState->y*$rate+$value->y*(1-$rate);
					 $location = "ST_GeomFromText('Point(".$state->x." ".$state->y.")')";
					 $state->height = $preState->height*$rate+$value->height*(1-$rate);
					 $ctime = $time+$videoItem->startTime;
					 $state->calcuCoverage();
					 $coverage = join(",",array_map("pt_cat",$state->getCoverage()));
					 //构造Fov
					 $coverage = "ST_GeomFromText('Polygon((".$coverage."))')";
					 $sql .= <<<EOF
					 INSERT INTO videocms_videostate(vid,focal,pan,tilt,maxdist,location,
				     height,time,coverage,parent)VALUES('$vid','{$state->focal}',
				     '{$state->pan}','{$state->tilt}','{$state->maxdist}',$location,
				     '{$state->height}','$ctime',$coverage,'{$preState->id}');	
EOF;
			
			 }
			//echo $sql."okkk";
			if(empty($sql))
			     continue;
			
			if(!query($this->_link,$sql))
				return FALSE;	
		   }
			$preState = $value;
		}
		
		return TRUE;	
	}
	/**
	 * 删除视频
	 * public function remove($id)
	 */
	public function remove($id){
		if(delete($this->_link,"videocms_video","vid = '".$id."'")
		 &&delete($this->_link,"videocms_videostate","vid = '".$id."'")){
			return TRUE;
		}
		 
		return FALSE;
	}
	/**
	 * 判断指定视频ID是否存在
	 * public function remove($id)
	 */
	public function checkExist($id){
		
	}
	/**
	 * 获取可编辑视频
	 * public function getEditVideo($id)
	*/
	public function getEditVideo($id){
		//建立新视频
		$sql = "SELECT * FROM videocms_video WHERE vid=".$id;
		$videoObj = fetchOne($this->_link,$sql);
		$videoItem = array(
			"id"=>$videoObj["vid"],
			"name"=>$videoObj["name"],
			"url"=>$videoObj["url"],
			"relateCamera"=>$videoObj["cid"],
			"startTime"=>$videoObj["starttime"],
			"endTime"=>$videoObj["endtime"],
			"states"=>array()
		);
		$ccdwidth = $videoObj["ccd_width"];
		$ccdheight = $videoObj["ccd_height"];
		
		$sql = "SELECT *,ST_X(location) as x,ST_Y(location) as y FROM videocms_videostate WHERE vid="
		       .$id." AND parent=-1 ORDER BY time ASC";
		$vsObjs = fetchAll($this->_link,$sql);
		
		foreach ($vsObjs as  $value) {
			$state = array("x"=>$value["x"],"y"=>$value["y"],
		   	"height"=>$value["height"],"pan"=>$value["pan"],
		   	"tilt"=>$value["tilt"],"time"=>$value["time"],
		   	"focal"=>$value["focal"],"maxdist"=>$value["maxdist"],
		   	"ccdwidth"=>$ccdwidth,
			"ccdheight"=>$ccdheight);
			array_push($videoItem["states"],$state);
		}
		return 	$videoItem;	
	}
}
/**
 * 视频类
 * class video
 **/
 class Video{
	public $id; //视频ID
	public $name; //视频名称
	public $url;  //视频Url
	public $ccdwidth; //靶面宽
	public $ccdheight;//靶面高
	public $startTime; //视频起始时间
	public $endTime; //视频终止时间
	private $_camera;  //关联摄像机
	private $_cameraType; //类型
	const CAMERA_TYPE_DYNAMIC = 2;  //动态相机类型
	const CAMERA_TYPE_STATIC = 0;  //固定相机类型
	const CAMERA_TYPE_PTZ = 1;  //PTZ相机类型
	public function __construct(){
		$_cameraType = Video::CAMERA_TYPE_DYNAMIC;
	}
	//获取关联摄像机
	public function getRelateCamera(){
		return $this->_camera;
	}
	
	//设置关联摄像机
	public function setRelateCamera($camera){
		//设置类型
		$this->camera = $camera;
		/*
		if(!empty($camera)){
			$this->ccdwidth = $camera["ccd_width"];
			$this->ccdheight = $camera["ccd_height"];
			if($camera["type"] == 0){
				$this->_cameraType == Video::CAMERA_TYPE_STATIC;
				$this->staticStates($camera);
			}else if($camera["type"] <= 2){
				$this->_cameraType == Video::CAMERA_TYPE_PTZ;
				$this->ptzStates($camera);
			}
			return TRUE;
		}else{
			return FALSE;
		}
		 * */
	}
	
	private $_states; //摄像机状态
	
	//设置状态
	public function setStates($states){
		if($this->_cameraType == Video::CAMERA_TYPE_DYNAMIC){
			$this->states = $states;
		}else if($this->_cameraType == Video::CAMERA_TYPE_PTZ){
			$this->states = $states;
			$this->ptzStates($this->_camera);
		}
	}
	//获取状态
	public function getStates($states){
		return $this->_states;
	}
	/*
	 * private function staticStates($cameraObj)
	 * @parama $cameraObj摄像机对象
	 * 设置静态状态
	 * */
	private function staticStates($cameraObj){
		$this->states = array();
		$tmpState = new VideoState();
		$tmpState->pan = $cameraObj["pan"];
		$tmpState->tilt = $cameraObj["tilt"];
		$tmpState->focal = $cameraObj["focal"];
		$tmpState->x = $cameraObj["x"];
		$tmpState->y = $cameraObj["y"];
		$tmpState->height = $cameraObj["z"];
		$tmpState->maxdist = $cameraObj["maxdist"];
		$tmpState->video = $this;
		$tmpState->time = $this->time;
		array_push($this->states,$tmpStates);
	}
	/*
	 * private function ptzStates($cameraObj)
	 * @parama $cameraObj摄像机对象
	 * 设置Fov状态，即位置不可变
	 * */
	private function ptzStates($cameraObj){
		for($i = 0;$i<count($this->states);$i++){
			$this->states[$i]->x = $cameraObj["x"];
			$this->states[$i]->y = $cameraObj["y"];
		}
	}
	
     /**
	 * 解析JSON
	 * public static function fromJSON($json)
	 */
	public function statesfromJSON($json){
		$statesOBJ = json_decode($json);
		//print_r($statesOBJ);
		$this->states = array();
		foreach($statesOBJ as $value){
			$state = new VideoState();
			$state->video = $this;
			$state->focal = $value->focal;
			$state->pan = $value->pan;
			$state->tilt = $value->tilt;
			$state->x = $value->x;
			$state->y = $value->y;
			$state->height = $value->z;
			$state->maxdist = $value->maxdist;
			$state->time = $value->time;
			
			$state->calcuCoverage();
			array_push($this->states,$state);
		}
	}
 }
/**
 * Video States
 * 视频状态类
 */
 class VideoState{
 	public $id;
 	public $focal; //焦距
 	public $pan; //方位角
 	public $tilt; //俯仰角
 	public $x;//x坐标
 	public $y;//y坐标
 	public $height;//架设高度
 	public $maxdist;//最大可视距离
 	public $time; //起始时间
 	public $video;   //关联Video
 	private $_coverage;//覆盖范围
 	
 	/*
	 * 获取最大覆盖范围
	 * public function getCoverage()
	 * */
 	public function getCoverage(){
 		return $this->_coverage;
 	}
	/**
	 * 计算覆盖范围
	 * public function calcuCoverage
	 */
	public function calcuCoverage(){
		$this->_coverage = array();
		$yAngle = atan2($this->video->ccdheight,$this->focal);
		$cy = tan($this->tilt*pi()/180-$yAngle)*$this->height;
		$fRay = sqrt($cy*$cy+$this->height*$this->height);
		$width = $fRay*$this->video->ccdwidth/$this->focal;
		array_push($this->_coverage,$this->transForm(array("x"=>-$width,"y"=>$cy)));
		array_push($this->_coverage,$this->transForm(array("x"=>$width,"y"=>$cy)));
		
		$cy = tan($this->tilt*pi()/180+$yAngle)*$this->height;
		$cy = ($cy < 0 || $cy > $this->maxdist) ? $this->maxdist : $cy;
		$fRay = sqrt($cy*$cy+$this->height*$this->height);
		$width = $fRay*$this->video->ccdwidth/$this->focal;
		array_push($this->_coverage,$this->transForm(array("x"=>-$width,"y"=>$cy)));
		array_push($this->_coverage,$this->transForm(array("x"=>$width,"y"=>$cy)));
		//闭合
		array_push($this->_coverage,$this->_coverage[0]);
	}
	/*
	 * 变换
	 * private function transForm($array)
	 * @pragma $array变换传入数组包含x,y
	 * */
	private function transForm($array){
		$x = $array["x"];
		$y = $array["y"];
		$array["x"] = $x*cos(-$this->pan*pi()/180)-$y*sin(-$this->pan*pi()/180)+$this->x;
		$array["y"] = $x*sin(-$this->pan*pi()/180)+$y*cos(-$this->pan*pi()/180)+$this->y;
		return $array;
	}
 }
 /**
  * 查询构造类
  * VideoQueryFilter
  */
 class VideoQueryFilter{
 	public $geometry; //空间条件
	public $property; //属性条件
	public $time;     //时间条件
	
	public static function fromGeoJson($json){
		$queryFilter = new VideoQueryFilter();
		//echo $json["properties"]["searchString"];
		$queryFilter->properties= isset($json["properties"]["searchString"])
		           ?  $json["properties"]["searchString"] : "";
		$startTime = strtotime($json["properties"]["startTime"]);
		$endTime = strtotime($json["properties"]["endTime"]);
		
		$queryFilter->time = "";
		if(!empty($startTime)&&!empty($endTime)){
			$queryFilter->time = array("startTime"=>$startTime,"endTime"=>$endTime);
		}
		
		$queryFilter->geometry = isset($json["geometry"])
	                          ? $json["geometry"] : "";
		return $queryFilter;
	}
/*****************************************
  解析属性查询
  public function LocationString($location)
 ****************************************/	
	public function whereString(){
		if(empty($this->properties)){
			return "";
	   }
		return "EXISTS(SELECT * FROM videocms_video WHERE ".
		       "name like '%{$this->properties}%')";
	}
/****************************************
 *解析时间查询 
 * ***************************************/
 public function timeString(){
		if(empty($this->time)){
		return "";
	   }
		return "time BETWEEN {$this->time["startTime"]} AND {$this->time["endTime"]}";
}
	 /*****************************************
  解析空间查询
  public function LocationString($location)
  *******************************/
  public function spatialString($spatial){
    if(empty($this->geometry)
	  ||empty($spatial)){
		return "";
	}
	$geoString = "ST_GeomFromGeoJSON('".json_encode($this->geometry)."')";
	return "ST_Intersects(".$geoString.",".$spatial.")";
  }
 }
 /**
  * 获取camera
  * function getCameraByID($id)
  */
 function getCameraByID($id){
 	global $link;
 	$sql = <<<EOF
 	SELECT *,ST_X(location) as x,ST_Y(location) as y,height as z,max_length as maxdist FROM videocms_camera WHERE id ='{$id}';
EOF;
	return fetchOne($link,$sql);
 }
