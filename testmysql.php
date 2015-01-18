<?php 
/*
$link = mysql_connect('localhost','root',''); 
if (!$link) { 
	die('Could not connect to MySQL: ' . mysql_error()); 
} 
echo 'Connection OK'; mysql_close($link); 
 * */
 //$connection_string="host=".DB_HOST." port=".DB_PORT." dbname=".DB_DBNAME." user=".DB_USER." password=".DB_PWD;
 //$link=pg_connect($connection_string)or die("数据库连接失败Error:".":".pg_errormessage());
 echo $_SERVER["HTTP_HOST"];
?> 