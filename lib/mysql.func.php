<?php
/**
 * 连接数据库
 * @return unknown
 */
function connect()
{
    $link=mysqli_connect(DB_HOST,DB_USER,DB_PWD) or die("数据库连接失败Error:".mysqli_errno().":".mysqli_error());
    mysqli_set_charset($link,DB_CHARSET);
    mysqli_select_db($link,DB_DBNAME) or die("指定数据库打开失败");
    return $link;
}

/**
 * 插入一条记录
 * @param unknown $table
 * @param unknown $array
 */
function insert($link,$table,$array)
{
    mysqli_query($link,"set names 'utf8'");
    $keys=join(",", array_keys($array));
    $values="'".join("','", array_values($array))."'";
    $sql="insert {$table}($keys)values({$values})";
    mysqli_query($link,$sql);
    return mysqli_insert_id($link);
}

//update imooc_admin set username='king' where id=1
/**
 * 记录的更新操作
 * @param string $table
 * @param array $array
 * @param string $where
 * @return number
 */
function update($link,$table,$array,$where=null){
	foreach($array as $key=>$val){
		if($str==null){
			$sep="";
		}else{
			$sep=",";
		}
		$str.=$sep.$key."='".$val."'";
	}
		$sql="update {$table} set {$str} ".($where==null?null:" where ".$where);
		$result=mysqli_query($link,$sql);
		//var_dump($result);
		//var_dump(mysql_affected_rows());exit;
		if($result){
			return mysqli_affected_rows($link);
		}else{
			return false;
		}
}

/**
 *	删除记录
 * @param string $table
 * @param string $where
 * @return number
 */
function delete($link,$table,$where=null){
    $where=$where==null?null:" where ".$where;
    $sql="delete from {$table} {$where}";
    mysqli_query($link,$sql);
    return mysqli_affected_rows($link);
}

/**
 *得到指定一条记录
 * @param string $sql
 * @param string $result_type
 * @return multitype:
 */
function fetchOne($link,$sql,$result_type=MYSQL_ASSOC){
    mysqli_query($link,"set names 'utf8'");
    $result=mysqli_query($link,$sql);
    $row=mysqli_fetch_array($result,$result_type);
    return $row;
}

/**
 * 得到结果集中所有记录 ...
 * @param string $sql
 * @param string $result_type
 * @return multitype:
 */
function fetchAll($link,$sql,$result_type=MYSQL_ASSOC){
    mysqli_query($link,"set names 'utf8'");
    $result=mysqli_query($link,$sql);
    while(@$row=mysqli_fetch_array($result,$result_type)){
        $rows[]=$row;
    }
    return $rows;
}

/**
 * 得到结果集中的记录条数
 * @param unknown_type $sql
 * @return number
 */
function getResultNum($link,$sql){
    $result=mysqli_query($link,$sql);
    return mysqli_num_rows($result);
}