<?php
//require_once '../include.php';

function addCamera($link)
{
    $arr=$_POST;
    $locationx=$arr['locationx'];
    $locationy=$arr['locationy'];
    $location="ST_GeomFromText('POINT({$locationx} {$locationy})')";
    
    if($arr['type']!=0)
    {
        $sql="insert into videocms_cameraadjust(id,max_tilt,min_tilt,max_rota,min_rota) values ('{$arr['id']}','{$arr['max_tilt']}'".
        ",'{$arr['min_tilt']}','{$arr['max_rota']}','{$arr['min_rota']}')";
        if(!query($link, $sql))
        {
           $mes="添加失败！<br/><a href='addCamera.php'>重新添加</a>";
           return $mes;
        }
    }
    $sql="insert into videocms_camera(type,state,location,height,".
         "ccd_width,ccd_height,id,name,pvgip,avpath,pan,tilt,focal,max_focal,min_focal,max_length,const_org,use_org,const_time) values".
         "('{$arr['type']}','{$arr['state']}',{$location},'{$arr['height']}','{$arr['ccd_width']}','{$arr['ccd_height']}','{$arr['id']}','{$arr['name']}',".
         "'{$arr['pvgip']}','{$arr['avpath']}','{$arr['pan']}','{$arr['tilt']}','{$arr['focal']}','{$arr['max_focal']}','{$arr['min_focal']}',".
         "'{$arr['max_length']}','{$arr['const_org']}','{$arr['use_org']}','{$arr['const_time']}')";
    if(query($link, $sql))
    {
        $mes="添加成功！<br/><a href='addCamera.php'>继续添加</a>|<a href='listCamera.php?page=1'>查看摄像机列表</a>";
    }else
    {
        $mes="添加失败！<br/><a href='addCamera.php'>重新添加</a>";
    }
    return $mes;
}

function delCam($link,$id)
{
    if(delete($link, "videocms_camera","id='{$id}'"))
    {
        delete($link, "videocms_cameraadjust","id='{$id}'");
        $mes="删除成功<br/><a href='listCamera.php?page=1'>查看摄像机列表</a>";
    }
    else
    {
        $mes="删除失败<br/><a href='listCamera.php?page=1'>查看摄像机列表</a>";
    }
    return $mes;
}

function editCamera($link,$id)
{
    $arr=$_POST;
    if($arr['type']==0)
    {
        delete($link, "videocms_cameraadjust","id='{$id}'");
    }else
    {
        $sql="select id from videocms_cameraadjust where id='".$arr['id']."'";
        if(getResultNum($link, $sql)==0)
        {

            $sql="insert into videocms_cameraadjust(id,max_tilt,min_tilt,max_rota,min_rota) values ('{$arr['id']}','{$arr['max_tilt']}',"
                ."'{$arr['min_tilt']}','{$arr['max_rota']}','{$arr['min_rota']}')";
            if(!query($link, $sql))
            {
                $mes="修改失败！<br/><a href='editCamera.php?id={$arr['id']}'>重新修改</a>";
                return $mes;
            }
        }else 
        {
            $sql="update videocms_cameraadjust set max_tilt='{$arr['max_tilt']}',min_tilt='{$arr['min_tilt']}'"
                .",max_rota='{$arr['max_rota']}',min_rota='{$arr['min_rota']}' where id='{$arr['id']}'";
        if(!query($link, $sql))
            {
                $mes="修改失败！<br/><a href='editCamera.php?id={$arr['id']}'>重新修改</a>";
                return $mes;
            }
        }
    }
    $sql=<<<EOF
    update videocms_camera set type={$arr['type']},state={$arr['state']},location=ST_GeomFromText('POINT({$arr['locationx']} {$arr['locationy']})')
    ,height='{$arr['height']}',ccd_width='{$arr['ccd_width']}',ccd_height='{$arr['ccd_height']}',name='{$arr['name']}',
     pvgip='{$arr['pvgip']}',avpath='{$arr['avpath']}',pan='{$arr['pan']}',tilt='{$arr['tilt']}',focal='{$arr['focal']}',
     max_focal='{$arr['max_focal']}',min_focal='{$arr['min_focal']}',max_length='{$arr['max_length']}',const_org='{$arr['const_org']}'
     ,use_org='{$arr['use_org']}',const_time='{$arr['const_time']}' where id='{$arr['id']}'
EOF;
    if(!query($link, $sql))
    {
        $mes="修改失败！<br/><a href='editCamera.php?id={$arr['id']}'>重新修改</a>";
        return $mes;
    }
    else
    {
        $mes="修改成功！<br/><a href='listCamera.php?page=1'>查看摄像机列表</a>";
        return $mes;
    }
}

function SelectCameraInnerParametery($link,$id)
{
    $sql="select ccd_width,ccd_height,focal from videocms_camera where id='".$id."'";
    $result = query($link, $sql);
    $row=pg_fetch_array($result);
    return $row;
}

function updateCameraAfterdemarcate($link,$id,$x,$y,$height,$pan,$tilt)
{
    $sql="update videocms_camera set location=ST_GeomFromText('POINT({$x} {$y})'),height='{$height}',pan='{$pan}',tilt='{$tilt}' where id='{$id}'";
    if(query($link, $sql))
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 检查摄像机是否存在
 * @param unknown $link
 * @param unknown $id
 * @return boolean
 */
function checkCamera($link,$id)
{
    $sql="select * from videocms_camera where id='".$id."'";
    if(!fetchOne($link, $sql))
    {
        return false;
    }
    else 
    {
        return true;
    }
}

/**
 * 添加摄像机预置位
 * @param unknown $link
 * @param unknown $cid
 */
function addReset($link,$cid)
{
    $arr=$_POST;
    $sql="insert into videocms_reset (fromtime,endtime,cid,pan,tilt,focal) values ('{$arr['fromtime']}','{$arr['endtime']}','{$cid}','{$arr['pan']}','{$arr['tilt']}','{$arr['focal']}')";
    if(query($link, $sql))
    {
        $mes="添加成功！<br/><a href='addReset.php?cid={$cid}'>继续添加</a>|<a href='resetCamera.php?id={$cid}'>查看预置位列表</a>";
    }else
    {
        $mes="添加失败！<br/><a href='addReset.php?cid={$cid}'>重新添加</a>";
    }
    return $mes;
}

function delReset($link,$id)
{
    $sql="select cid from videocms_reset where id='".$id."'";
    $row=fetchOne($link, $sql);
    $cid=$row['cid'];
    if(delete($link, "videocms_reset","id={$id}"))
    {
        $mes="删除成功<br/><a href='resetCamera.php?id={$cid}'>查看预置位列表</a>";
    }
    else
    {
        $mes="删除失败<br/><a href='resetCamera.php?id={$cid}'>查看预置位列表</a>";
    }
    return $mes;
}

function updateReset($link,$id,$arr)
{
    $sql="update videocms_reset set fromtime='{$arr['fromtime']}',endtime='{$arr['endtime']}',tilt='{$arr['tilt']}',pan='{$arr['pan']}',focal='{$arr['focal']}' where id='".$id."'";
    
    $cidsql="select cid from videocms_reset where id='".$id."'";
    $row=fetchOne($link, $cidsql);
    $cid=$row['cid'];
    
    if(!query($link, $sql))
    {
        $mes="修改失败！<br/><a href='editReset.php?id={$id}'>重新修改</a>";
        return $mes;
    }
    else
    {
        $mes="修改成功！<br/><a href='resetCamera.php?id={$cid}'>查看预置位列表</a>";
        return $mes;
    }
    return $mes;
}