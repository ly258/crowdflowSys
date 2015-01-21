<?php
function showPage($page,$totalPage,$where=null,$sep="&nbsp"){
	$where=($where==null)?null:"&".$where;
	$url=$_SERVER['PHP_SELF'];
	$index=($page==1)?"首页":"<a href='{$url}?page=1{$where}'>首页</a>";
	$last=($page==$totalPage)?"尾页":"<a href='{$url}?page={$totalPage}{$where}'>尾页</a>";
	$prev=($page==1)?"上一页":"<a href='{$url}?page=".($page-1)."{$where}'>上一页</a>";
	$next=($page==$totalPage)?"下一页":"<a href='{$url}?page=".($page+1)."{$where}'>下一页</a>";
	$str = "总共{$totalPage}页/当前是第{$page}页";
	$p=null;
	for ($i=1; $i<=$totalPage; $i++) { 
		if ($page==$i) {
			$p.="[{$page}]";
		}
		else
		{
			$p.="<a href='{$url}?page={$i}{$where}'>[{$i}]</a>";
		}
	}
	$pageStr=$str.$sep.$index.$sep.$prev.$sep.$p.$sep.$next.$sep.$last;
	return $pageStr;
}
?>