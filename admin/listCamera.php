<?php
   require_once '../include.php';
   checklogined();
   
   $sql="select type from videocms_camera";
   $rows = fetchAll($link, $sql);
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
  <title></title>
  <link href="styles/listCamera.css" rel="stylesheet" type="text/css" />
  <script type="text/javascript" src = "../scripts/OpenLayers.debug.js"></script>
  <script type="text/javascript" src ="../scripts/jquery-1.4.1.min.js"></script>
  <script type="text/javascript" src ="../scripts/MapConfig.js"></script>
  <script type="text/javascript" src ="../scripts/camera.js"></script>
  <script type="text/javascript">
      var cameraViewer;
      function init(){
        map = MapConfig.createMap("map");       
        
        //var cameras = OpenLayers.Camera.Prase(DemoCameraCollectionJson);
        var cameraQueryer = new OpenLayers.CameraQueryer();
        cameraQueryer.queryurl="../cameraQuery.php";
        var cameraCollection = new OpenLayers.CameraCollection([]);
        var cameraList = new OpenLayers.CameraList("rsList");
        cameraList.defaultStyle=function(i)
        {
          var cameras = this.cameras;
          var typeName,stateName;
          var disabled=(cameras[i].type==0) ?"disabled":"";
          var style= $("<div id="+i+" class=\"item\" style=\"width:100%;height:110px;cursor:pointer;border-top:1px solid #000;\">"
              +"<table width=\"100%\" height=\"100%\">"
              +"  <tr><td  width=\"40\" align=\"center\" align=\"center\" valign=\"center\">"+(i+1)+"</td>"
              +"     <td>"
              +"    <table width=\"100%\" height=\"100%\" style=\"font-size:12px\">"
              +"      <tr>"
              +"      <td colspan=\"2\" style=\"font-size:14px\">"+cameras[i].id+"</td>"
              +"        </tr>"
              +"        <tr>"
              +"          <td width=\"30\">名称:</td>"
              +"          <td colspan=\"3\">"+cameras[i].name+"</td>"
              +"        </tr>"
              +"        <tr>"
              +"           <td colspan=\"2\" style=\"font-size:14px\"><input style=\"width:60px;\" type=\"button\" value=\"修改\""
              +"           class=\"btn\" onclick=\"editCamera('"+cameras[i].id+"')\"><input style=\"width:60px;\" type=\"button\" value=\"删除\""
              +"           class=\"btn\" onclick=\"delCamera('"+cameras[i].id+"')\"></td>"
              +"        </tr>"
              +"        <tr>"
              +"           <td colspan=\"2\" style=\"font-size:14px\"><input style=\"width:60px;\" type=\"button\" value=\"标定\""
              +"           class=\"btn\" onclick=\"calCamera('"+cameras[i].id+"')\"><input id=\"camera"+cameras[i].id+"\" style=\"width:60px;\" type=\"button\" value=\"预置位\""
              +"           class=\"btn\" onclick=\"resetCamera('"+cameras[i].id+"')\" "+disabled+"></td>"
              +"        </tr>"
              +"      </table>"
              +"    </td>"
              +"    <td align=\"center\">"
              +"      <img src=\"./images/"+(cameras[i].type*5+cameras[i].tstate)+".png\" width=\"50\" height=\"40\"/>"
              +"    </td>"            
              +"  </tr>"
              +"</table>"
            +"</div>");
          
          return style;
        };
        cameraViewer = new OpenLayers.CameraViewer(cameraCollection,cameraList,cameraQueryer);
        cameraViewer.attach(map);
        //
        cameraQueryer.query();
        //cameraViewer.setCameras(cameras);
        //cameraInit();
        //controlInit();

        
      }

      function modeChange(element){
      cameraViewer.cameraQueryer.setQueryMode(element.value);
      }
      
      function search(){
      cameraViewer.cameraQueryer.query($("#searchTxt").val());
      }

      function editCamera(id)
     {
        window.location="editCamera.php?id="+id;
     }

     function delCamera(id)
     {
       if(confirm("您确定要删除吗？"))
       {
           window.location="doAdminAction.php?act=delCamera&id="+id;
       }    
     }

     function calCamera(id)
     {
    	 window.location="calCamera.php?id="+id;
     }

     function resetCamera(id)
     {
    	 window.location="resetCamera.php?id="+id;   
     }
      </script>
</head>
<body align="center" onload="init()">
  <div id="searth" class="searchContainer">
    <input id ="searchTxt" type="text"/>
    <input  id="searchBtn"  type="button" style="height:30px" value="搜 索" onclick="search()"/><br/>
    <div id="searchtype">
      <input  id="point" name = "georadio" type="RADIO" value="point" checked="checked" onclick="modeChange(this);"/>点选&nbsp;&nbsp;
      <input  id="rect" name = "georadio" type="RADIO" value="rect" onclick="modeChange(this);"/>矩形&nbsp;&nbsp;
      <input  id="polygon" name = "georadio" type="RADIO" value="polygon" onclick="modeChange(this);"/>多边形&nbsp;&nbsp;<br/>
    </div>
  </div>
  <div class="mainContainer">
    <div id="rsList" class="tocContainer"></div>
    <div id="map" class="mapContainer"></div>
  </div>
</body>
</html>