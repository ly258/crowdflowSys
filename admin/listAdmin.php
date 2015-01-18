<?php
 require_once '../include.php';
 checklogined();
 $sql="select id,username from videocms_admin";
 $totalRows=getResultNum($link,$sql);
 $pageSize=8;
 $page=$_REQUEST['page']?(int)$_REQUEST['page']:1;
 $totalPage=ceil($totalRows/$pageSize);

 $offset=($page-1)*$pageSize;
 $sql="select * from videocms_admin LIMIT {$pageSize} OFFSET {$offset}";
 $rows=fetchAll($link,$sql);
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
                                <th width="15%">编号</th>
                                <th width="25%">管理员名称</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $row):?>
                            <tr>
                                <!--这里的id和for里面的c1 需要循环出来-->
                                <td><input type="checkbox" id="c1" class="check"><label for="c1" class="label"><?php echo $row["id"]?></label></td>
                                <td><?php echo $row['username']?></td>
                                <td align="center"><input type="button" value="修改" class="btn" onclick="editAdmin(<?php echo $row['id'];?>)"><input type="button" value="删除" class="btn" onclick="delAdmin(<?php echo $row['id'];?>)"></td>
                            </tr>
                       <?php endforeach;?>
                       <?php if($rows>$pageSize):?>
                            <tr>
                                <td colspan=4><?php echo showPage($page,$totalPage)?></td>
                            </tr>
                        <?php endif;?>  
                        </tbody>
                    </table>
                </div>
  </body>
  <script type="text/javascript">
     function editAdmin(id)
     {
        window.location="editAdmin.php?id="+id;
     }

     function delAdmin(id)
     {
       if(confirm("您确定要删除吗？"))
       {
           window.location="doAdminAction.php?act=delAdmin&id="+id;
       }    
     }
  </script>
</html>