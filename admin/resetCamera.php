<?php
require_once '../include.php';
checklogined();
$cid=$_GET['id'];
$sql = "select * from videocms_reset where cid='".$cid."'";
$rows = fetchAll($link, $sql);
if(!$rows)
    alertMes("没有预置位，请添加！", "addReset.php?cid={$cid}");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
<title></title>
<link rel="stylesheet" href="styles/backstage.css">
</head>
<body>
	<div class="details">
		<!--表格-->
		<table class="table" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th width="15%">开始时间</th>
					<th width="15%">结束时间</th>
					<th width="15%">旋转角</th>
					<th width="15%">方位角</th>
					<th width="15%">焦距</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
                        <?php foreach ($rows as $row):?>
                <tr>
					<!--这里的id和for里面的c1 需要循环出来-->
					<td><?php echo $row['fromtime']?></td>
					<td><?php echo $row['endtime']?></td>
					<td><?php echo $row['pan']?>度</td>
					<td><?php echo $row['tilt']?>度</td>
					<td><?php echo $row['focal']?></td>
					<td align="center"><input type="button" value="添加" class="btn"
						onclick="addReset('<?php echo $row['cid'];?>')"><input type="button" value="修改" class="btn"
						onclick="editReset(<?php echo $row['id'];?>)"><input type="button"
						value="删除" class="btn"
						onclick="delReset(<?php echo $row['id'];?>)"></td>
				</tr>
                       <?php endforeach;?>  
           </tbody>
		</table>
	</div>
</body>
<script type="text/javascript">
     function addReset(cid)
     {
        window.location="addReset.php?cid="+cid;
     }


     function editReset(id)
     {
        window.location="editReset.php?id="+id;
     }

     function delReset(id)
     {
       if(confirm("您确定要删除吗？"))
       {
           window.location="doAdminAction.php?act=delReset&id="+id;
       }    
     }
  </script>
</html>