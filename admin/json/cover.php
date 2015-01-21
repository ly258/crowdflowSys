<?php
if(!array_key_exists('id',$_GET)){
    echo '0';
    exit();
}

$id = $_GET['id'];

$path = dirname(__FILE__);
system("python ".$path."/fullCover.py --id ".$id);
?>
