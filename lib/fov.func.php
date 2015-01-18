<?php
include_once dirname(__FILE__).'/../configs/configs.php';
include_once dirname(__FILE__).'/pgsql.func.php';
function fov_full_geojson($id){
    return fov_geojson('full',$id);
}

function fov_real_geojson($id){
    return fov_geojson('real',$id);
}

function fov_geojson($type,$id){
    $sql = "select ST_AsGeoJSON(fov_".$type.") as geojson from videocms_fov where id = '".$id."'";
    $link = connect();
    $result = pg_query($link,$sql);
    if(pg_num_rows($result)==0){
        return "";
    }
    $row = pg_fetch_row($result);
    return $row[0];
}
?>
