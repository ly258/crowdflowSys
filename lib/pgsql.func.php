<?php
/**
 * 连接数据库
 * @return unknown
 */
function connect()
{
    $connection_string="host=".DB_HOST." port=".DB_PORT." dbname=".DB_DBNAME." user=".DB_USER." password=".DB_PWD;
    $link=pg_connect($connection_string)or die("数据库连接失败Error:".":".pg_errormessage());
    return $link;
}

/**
 * 插入一条记录
 * @param unknown $table
 * @param unknown $array
 */
function insert($link,$table,$array)
{
    $keys=join(",", array_keys($array));
    $values="'".join("','", array_values($array))."'";
    $sql="insert into {$table}($keys)values({$values})";
    $result=pg_query($link,$sql);
    return pg_affected_rows($result);
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
    $str=null;
	foreach($array as $key=>$val){
		if($str==null){
			$sep="";
		}else{
			$sep=",";
		}
		$str.=$sep.$key."='".$val."'";
	}
		$sql="update {$table} set {$str} ".($where==null?null:" where ".$where);
		$result=pg_query($link,$sql);
		//var_dump($result);
		//var_dump(mysql_affected_rows());exit;
		if($result){
			return pg_affected_rows($result);
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
    $result=pg_query($link,$sql);
    return pg_affected_rows($result);
}

/**
 *得到指定一条记录
 * @param string $sql
 * @param string $result_type
 * @return multitype:
 */
function fetchOne($link,$sql){
    $result=pg_query($link,$sql);
    $row=pg_fetch_array($result);
    return $row;
}

/**
 * 得到结果集中所有记录 ...
 * @param string $sql
 * @param string $result_type
 * @return multitype:
 */
function fetchAll($link,$sql){
    $result=pg_query($link,$sql);
    $rows=pg_fetch_all($result);
    return $rows;
}
/**
 * 获取一个行的值
 * @param string $sql
 * @param string $result_type
 */
function fetchArray($query){
    $row=pg_fetch_array($query);
    return $row;
}
/**
 * 得到结果集中的记录条数
 * @param unknown_type $sql
 * @return number
 */
function getResultNum($link,$sql){
    $result=pg_query($link,$sql);
    return pg_num_rows($result);
}


function query($link,$sql) 
{ 
    $result=pg_query($link,$sql); 
    return $result;
}