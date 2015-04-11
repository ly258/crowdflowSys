<?php
	include_once("camera.search.inc.php");
	/***********************************
	*class Transform
	*变换
	*************************************/
	class Transform{
		public $focal; //焦距
		public $pan; //方位角
		public $tilt; //俯仰角
		public $x;//x坐标
		public $y;//y坐标
		public $height;//架设高度
		public $maxdist;//最大可视距离
		public $ccdWidth;//靶面宽度
		public $ccdHeight;//靶面高度
		
		public function trans($pt){
			
			$yAngle = atan2($this->ccdHeight/2-$pt["y"],$this->focal);
			$cy = tan($this->tilt*pi()/180+$yAngle)*$this->height;
			//echo $cy."<br/>";
			//大于最大可视范围
			if($cy > $this->maxdist || $cy <= 0){
				$cy = -1;
			}

			$fRay = sqrt($cy*$cy+$this->height*$this->height);
			$cx = $fRay*($pt["x"]-$this->ccdWidth/2)/$this->focal;
			
			$x = $cx; $y = $cy;
			$cx = $x*cos(-$this->pan*pi()/180)-$y*sin(-$this->pan*pi()/180)+$this->x;
			$cy = $x*sin(-$this->pan*pi()/180)+$y*cos(-$this->pan*pi()/180)+$this->y;
			return array("x"=>$cx,"y"=>$cy);
		}
		
	}
	/*************************************************
	*查询人数及方向
	*function searchPeople($link,$queryFilter)
	*************************************************/
	function searchPeople($link,$queryFilter){
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
			   const_org,use_org,const_time,alarm,max_tilt,min_tilt,max_rota,min_rota   
			   FROM videocms_camera LEFT OUTER JOIN "rt_monitorCfg"
			   ON videocms_camera.id = "rt_monitorCfg"."cameraId" LEFT OUTER JOIN videocms_cameraadjust
			   ON videocms_camera.id = videocms_cameraadjust.id {$where}
EOF;
		$cameras = fetchAll($link,$sql);			
		
		//查选人数rst
		$redis = new redis();  
		$redis->connect('127.0.0.1', 6379);  
		$cameraid = array();
	    for($i=0;$i<$redis->lsize("cameraid");$i++)
	    {
	        array_push($cameraid,$redis->lget('cameraid',$i));
	    }

		//构造GeoJson
		$cameraGeoJson = array("type"=>"FeatureCollection","features"=>array());
		foreach($cameras as $value){
			$feature = array(
			"type"=>"Feature",
			"geometry"=>array(
			  "type"=>"Point",
			  "coordinates"=>array($value["x"],$value["y"])
			),
			"properties"=>array(
				"id"=>$value["id"],
				"name"=>$value["name"],
				"ip"=>$value["pvgip"],
				"avpath"=>$value["avpath"],
				"type"=>$value["type"],
				"state"=>$value["state"],
				"height"=>$value["height"],
				"pan"=>$value["pan"],
				"minpan"=>$value["min_rota"],
				"maxpan"=>$value["max_rota"],
				"tilt"=>$value["tilt"],
				"mintilt"=>$value["max_tilt"],
				"maxtilt"=>$value["min_tilt"],
				"focal"=>$value["focal"],
				"minfocal"=>$value["max_focal"],
				"maxfocal"=>$value["min_focal"],
				"ccdwidth"=>$value["ccd_width"],
				"ccdheight"=>$value["ccd_height"],
				"maxdist"=>$value["max_length"],
				"constorg"=>$value["const_org"],
				"useorg"=>$value["use_org"],
				"consttime"=>$value["const_time"],
				"alarm"=>$value["alarm"]
			));
			
			if(in_array($value["id"],$cameraid)){
				$feature["properties"]["num"] = (int)$redis->get($value["id"]."@number");
				//$feature["properties"]["people"] = $prst[$value["id"]]["people"];
				
				$transForm = new Transform();
				$transForm->focal = $value["focal"];
				$transForm->pan = $value["pan"];
				$transForm->tilt = $value["tilt"];
				$transForm->x = $value["x"];
				$transForm->y = $value["y"];
				$transForm->maxdist = $value["max_length"];
				$transForm->height = $value["height"];
				$transForm->ccdWidth = $value["ccd_width"];
				$transForm->ccdHeight = $value["ccd_height"];
				$feature["properties"]["people"] = array();
				$feature["properties"]["startpoint"] = array();
				$feature["properties"]["endpoint"] = array();
				//变换为地理坐标
				//查询人群坐标;
				for($i=0;$i<$redis->lsize($value["id"]."@X");$i++){
					$coord = array("x"=>$redis->lget($value["id"]."@X",$i),"y"=>$redis->lget($value["id"]."@Y",$i));
					$coord = $transForm->trans($coord);
					//echo($coord);
					//print_r($redis->lget($value["id"]."@X",$i));
					array_push($feature["properties"]["people"], $coord);
				}
				//查询人群方向坐标
				for($i=0;$i<$redis->lsize($value["id"]."@startX");$i++){
				    $directionStartCoord = array("x"=>$redis->lget($value["id"]."@startX",$i),"y"=>$redis->lget($value["id"]."@startY",$i));
				    $directionStartCoord = $transForm->trans($directionStartCoord);
				    $directionEndCoord = array("x"=>$redis->lget($value["id"]."@endX",$i),"y"=>$redis->lget($value["id"]."@endY",$i));
				    $directionEndCoord = $transForm->trans($directionEndCoord);
				    //echo($directionEndCoord);
				    //print_r($directionEndCoord);
				    array_push($feature["properties"]["startpoint"], $directionStartCoord);
				    array_push($feature["properties"]["endpoint"], $directionEndCoord);
				}
			}else{
				$feature["properties"]["num"] = -1;
				$feature["properties"]["people"] = array();
				$feature["properties"]["startpoint"] = array();
				$feature["properties"]["endpoint"] = array();
			}

			array_push($cameraGeoJson["features"],$feature);
		}
		//return $directionStartCoord;
		return json_encode($cameraGeoJson);
	}