<?php
//require_once 'include.php';
/**
 * @param unknown $sql
 */
function checkAdmin($link,$sql)
{
    return fetchOne($link,$sql);
}

function checklogined()
{
    if($_SESSION['adminId']==""&&$_COOKIE['adminId']=="")
    {
        alertMes("请先登录！", "login.php");
    }
}


function addAdmin($link)
{
    $arr=$_POST;
    $arr["password"]=md5($_POST["password"]);
    if(insert($link,"videocms_admin", $arr))
    {
        $mes="添加成功！<br/><a href='addAdmin.php'>继续添加</a>|<a href='listAdmin.php?page=1'>查看管理员列表</a>";
    }else
    {
        $mes="添加失败！<br/><a href='addAdmin.php'>重新添加</a>";
    }
    return $mes;
}


function logout()
{
    $_SESSION=array();
    if(isset($_COOKIE[session_name()]))
    {
        setcookie(session_name(),"",time()-1);
    }
    if(isset($_COOKIE['adminId']))
    {
        setcookie("adminId","",time()-1);
    }
    if(isset($_COOKIE['adminName']))
    {
        setcookie("adminName","",time()-1);
    }
    session_destroy();
    header("location:login.php");
}

function getAllAdmin($link)
{
    $sql="select id,username,password from videocms_admin";
    $rows=fetchAll($link, $sql);
    return $rows;
}

function editAdmin($link,$id)
{
    $arr=$_POST;
    $arr['password']=md5($_POST['password']);
    if(update($link,"videocms_admin", $arr,"id='{$id}'"))
    {
        $mes="编辑成功<br/><a href='listAdmin.php?page=1'>查看管理员列表</a>";
    }else 
    {
        $mes="编辑失败<br/><a href='listAdmin.php?page=1'>返回管理员列表，重新修改</a>";
    }
    return $mes;
    
}

function delAdmin($link,$id)
{
    if(delete($link, "videocms_admin","id={$id}"))
    {
        $mes="删除成功<br/><a href='listAdmin.php?page=1'>查看管理员列表</a>";
    }
    else 
    {
        $mes="删除失败<br/><a href='listAdmin.php?page=1'>查看管理员列表</a>";
    }
    return $mes;
}

function setcoorsystem($link,$arr)
{
    if(update($link,"videocms_measurecoor", $arr,"id='1'"))
    {
        $mes="修改坐标系成功<br/><a href='setMeasureCoor.php'>返回</a>";
    }else
    {
        $mes="修改坐标系失败<br/><a href='setMeasureCoor.php'>返回/a>";
    }
    return $mes;
}