<?php
	include_once("./include.php");
	include_once("./core/people2.inc.php");
	
	$filter = new CameraQueryFilter();
	if(!empty($_POST)){
		$filter = CameraQueryFilter::fromGeoJson($_POST);
	}
	//echo searchPeople($link,$filter);
	print_r(searchPeople($link,$filter));