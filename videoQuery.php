<?php
  include_once("./include.php");
  include_once("./core/video.inc.php");
  //print_r($_POST);
  $videoDB = new VideoDB($link);
  $filter = new VideoQueryFilter();
  if(!empty($_POST)){
	$filter = VideoQueryFilter::fromGeoJson($_POST);
  }
  $videoArray = $videoDB->query($filter);
  echo json_encode($videoArray);
?>