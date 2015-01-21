<?php
  include_once("../include.php");
  include_once("../core/video.inc.php");
  //print_r($_POST);
  $id = $_GET["id"];
  $videoDB = new VideoDB($link);
  $videoArray = $videoDB->getEditVideo($id);
  echo json_encode(array($videoArray));
   