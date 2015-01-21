<?php
require_once './include.php';
require_once './core/video.measure.inc.php';
$act=$_REQUEST['act'];
$act=$act?$act:"default";
$sql="select *from videocms_measurecoor where id='1'";
$coordinate=fetchOne($link, $sql);

switch ($act)
{
    case "checkcameraid":
        $id=$_REQUEST['id'];
        if(checkCamera($link,$id))
        {
            echo "existed";
        }
        else
            echo "non-exist";
        break;
    
    case "obtainvideosize":
        $id=$_REQUEST['id'];
        $sql="select ccd_width,ccd_height,starttime from videocms_video where vid='".$id."'";
        $videosize = fetchOne($link, $sql);
        echo json_encode($videosize);
        break;
        
    case "SpacePosCal":        
        $para=$_POST;
        $pointmeasure = new measure();
        $rows=$pointmeasure->obtainparameter($link,$para);       
        $coor=$pointmeasure->calculate($act, $para, $rows,$coordinate);
        echo json_encode($coor);
        break;

    case "HeightCal":
        $para=$_POST;
        $heightmeasure = new measure();
        $rows=$heightmeasure->obtainparameter($link, $para);
        $hobj=$heightmeasure->calculate($act, $para, $rows,$coordinate);
        echo json_encode($hobj);
        break;
        
    case "SpaceHeiCal":
        $para=$_POST;
        $spaceheimeasure=new measure();
        $rows=$spaceheimeasure->obtainparameter($link, $para);
        $spacehei=$spaceheimeasure->calculate($act, $para, $rows,$coordinate);
        echo json_encode($spacehei);
        break;
        
    case "DistanceCal":
        $para=$_POST;
        $distancemeasure=new measure();
        $rows=$distancemeasure->obtainparameter($link, $para);
        $dist = $distancemeasure->calculate($act, $para, $rows,$coordinate);
        echo json_encode($dist);
        break;
        
    case "SpaceDistanceCal":
        $para=$_POST;
        $spacedistmeasure=new measure();
        $rows=$spacedistmeasure->obtainparameter($link, $para);
        $spacedist = $spacedistmeasure->calculate($act, $para, $rows,$coordinate);
        echo json_encode($spacedist);
        break;
        
    case "default":
        die("nothing");
        break;
}

