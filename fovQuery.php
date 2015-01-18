<?php
include_once(dirname(__FILE__).'/lib/fov.func.php');
function error_404(){
    header('HTTP/1.1 404 NotFound');
    header('status: 404 Not Found');
    exit(0);
}

if(!array_key_exists('id',$_GET)){
    error_404();
}

if(!array_key_exists('type',$_GET)||($_GET['type']!='full' && $_GET['type']!='real')){
    error_404();
}

$geojson = fov_geojson($_GET['type'],$_GET['id']);
if($geojson == ""){
    error_404();
}
echo $geojson;
?>
