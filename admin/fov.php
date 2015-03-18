<?php
require_once '../include.php';
checklogined();
if(array_key_exists('p',$_GET) && is_numeric($_GET['p'])){
    $page = intval($_GET['p']);
}else{
    $page = 1;
}
if(array_key_exists('pn',$_GET) && is_numeric($_GET['pn'])){
    $num = intval($_GET['pn']);
}else{
    $num = 15;
}
$sql="select * from videocms_camera order by id limit {$num} offset ".(($page-1)*$num);
$rows = fetchAll($link,$sql);
$sql2 = "select id from videocms_fov";
$all_id = fetchAll($link,$sql2);
$idarr = array();
foreach($all_id as $id){
    $idarr[]=$id['id'];
}
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
  <title></title>
  <script type="text/javascript" src ="./scripts/jquery-1.11.2.min.js"></script>
  <script type="text/javascript">
function calcfov(cid,bid)
{
    $('#'+bid).attr('disabled',true);
    $.ajax({
        type: "GET",
        url: "./json/cover.php?id="+cid,
        dataType: "text",
        success: function(data){
                if(data[0]=='1')
                    $('#l'+bid).html("√ 已计算");
                else{
                    $('#l'+bid).html("计算出错");
                    $('#'+bid).removeAttr("disabled");
                }                    
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown){ 
            alert("查询失败");
            $('#l'+bid).html("计算出错");
            $('#'+bid).removeAttr("disabled");
        }
    });
}
  </script>
</head>
<body>
<table>
<?php
$index = 1;
foreach($rows as $row){
    $flag = in_array($row['id'],$idarr);
?>
    <tr><td><?php echo $index; ?></td>
        <td><?php echo $row['id']."<br />".$row['name'] ?></td>
        <td><img src="./images/<?php echo $row['type']*5+$row['state']; ?>.png" width="50" height="40"/></td>
        <td><div id="lb<?php echo $index; ?>"><?php echo $flag?"√ 已计算":"× 未计算"; ?></div></td>
        <td><input type="button" id="b<?php echo $index; ?>" value="计算覆盖范围" onclick="javascript:calcfov('<?php echo $row['id'] ?>','b<?php echo $index; ?>')" /></td>
    </tr>
<?php
    $index++;
}
?> 
</table>
</body>
</html>
