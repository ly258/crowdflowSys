<?php
  include_once("./include.php");
  include_once("./core/camera.search.inc.php");
  
  //print_r($_POST);
  $filter = new CameraQueryFilter();
  if(!empty($_POST)){
	$filter = CameraQueryFilter::fromGeoJson($_POST);
  }
  echo searchCamera($link,$filter);