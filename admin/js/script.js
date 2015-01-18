// variables
var canvas, ctx;
var image;
var iMouseX, iMouseY = 1;
var bMouseDown = false;
var iZoomRadius = 100;
var iZoomPower = 2;
var fr;
var MouseClickArr;
var vectorLayer;
var layer_style;
var style_mark;
var is_image_loaded;
var polygon = [];
var cameraL = [];
var cameraH = -1 , cameraP , cameraT;

// drawing functions
function clear() { // clear canvas function
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
}

function drawScene() { // main drawScene function
    clear(); // clear canvas
    // draw corss
    if(MouseClickArr.length){
            
	    var i;
	    ctx.lineWidth = 3; 
	    ctx.strokeStyle = "red";  
	    ctx.beginPath();  
            var features = [];
	    for(i=0;i<MouseClickArr.length;i++){
		ctx.moveTo(MouseClickArr[i][0]-10,MouseClickArr[i][1]);  
		ctx.lineTo(MouseClickArr[i][0]+10,MouseClickArr[i][1]); 
		ctx.moveTo(MouseClickArr[i][0],MouseClickArr[i][1]-10);  
		ctx.lineTo(MouseClickArr[i][0],MouseClickArr[i][1]+10); 
	    }
            if(features.length){
                vectorLayer.addFeatures(features);
	    }
	    ctx.stroke();
    }
    if (bMouseDown) { // drawing zoom area
        ctx.drawImage(image, 0 - iMouseX * (iZoomPower - 1), 0 - iMouseY * (iZoomPower - 1), ctx.canvas.width * iZoomPower, ctx.canvas.height * iZoomPower);
        ctx.globalCompositeOperation = 'destination-atop';

        var oGrd = ctx.createRadialGradient(iMouseX, iMouseY, 0, iMouseX, iMouseY, iZoomRadius);
        oGrd.addColorStop(0.8, "rgba(0, 0, 0, 1.0)");
        oGrd.addColorStop(1.0, "rgba(0, 0, 0, 0.1)");
        ctx.fillStyle = oGrd;
        ctx.beginPath();
        ctx.arc(iMouseX, iMouseY, iZoomRadius, 0, Math.PI*2, true); 
        ctx.closePath();
        ctx.fill();
    }

    
    
    // draw source image
    ctx.drawImage(image, 0, 0, ctx.canvas.width, ctx.canvas.height);
}

$(function(){
    init();
    is_image_loaded = false;
    //layer_style
    layer_style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
    //layer_style.fillOpacity = 0.2;
    //layer_style.graphicOpacity = 1;
    //renderer
    var renderer = OpenLayers.Util.getParameters(window.location.href).renderer;
    renderer = (renderer) ? [renderer] : OpenLayers.Layer.Vector.prototype.renderers;
    ///vectorLayer
    vectorLayer = new OpenLayers.Layer.Vector("Simple Geometry", {
                style: layer_style,
                renderers: renderer
            });
    map.addLayer(vectorLayer);
    //MakerLayer

    /*
     * Mark style
     */
    style_mark = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
    style_mark.graphicWidth = 16;
    style_mark.graphicHeight = 16;
    //style_mark.graphicXOffset = 10; // default is -(style_mark.graphicWidth/2);
    //style_mark.graphicYOffset = -style_mark.graphicHeight;
    style_mark.externalGraphic = "./images/cross.png";

    MouseClickArr = new Array();
    // loading source image
    image = new Image();
    image.onload = function () {
    }
    image.src = 'images/image.png';

    // creating canvas object
    canvas = document.getElementById('panel');
    ctx = canvas.getContext('2d');

    $('#panel').mousemove(function(e) { // mouse move handler
        var canvasOffset = $(canvas).offset();
        iMouseX = Math.floor(e.pageX - canvasOffset.left);
        iMouseY = Math.floor(e.pageY - canvasOffset.top);
    });

    $('#panel').mousedown(function(e) { // binding mousedown event
        bMouseDown = true;
    });

    $('#panel').mouseup(function(e) { // binding mouseup event
        bMouseDown = false;
        var canvasOffset = $(canvas).offset();
        iMouseX = Math.floor(e.pageX - canvasOffset.left);
        iMouseY = Math.floor(e.pageY - canvasOffset.top);
	MouseClickArr[MouseClickArr.length-1][0] = iMouseX;
	MouseClickArr[MouseClickArr.length-1][1] = iMouseY;
	MouseClickArr[MouseClickArr.length-1][2] = iMouseX*image.width/canvas.width;
	MouseClickArr[MouseClickArr.length-1][3] = iMouseY*image.height/canvas.height;
    });

    setInterval(drawScene, 30); // loop drawScene
});

function setImagePreview() {  
    var docObj = document.getElementById("doc");  
    var file = docObj.files[0];
    fr = new FileReader();
    fr.onload = createImage;
    fr.readAsDataURL(file);
    MouseClickArr = [];
    polygon = [];
    cameraL = [];
    vectorLayer.removeAllFeatures();
    
    is_image_loaded = true; 
    cameraH = -1;
}

function createImage() {
	image = new Image();
	image.onload = function () {};
	image.src = fr.result;
	canvas = document.getElementById('panel');
	ctx = canvas.getContext('2d');
}

function addArr(){
    if(is_image_loaded)
        MouseClickArr.push([-1,-1,-1,-1,-1,-1]);
}



///map
var map;
function init() {
    
    map = new OpenLayers.Map({
        div: "map",
        projection: "EPSG:900913"
    });    

    var wmts = new OpenLayers.Layer.WMTS({
        name: "Medford Buildings",
        url: "http://t1.tianditu.cn/img_w/wmts",
        layer: "img",
        matrixSet: "w",
        format: "tiles",
        style: "default",
        numZoomLevels:19
    });                

    map.addLayer(wmts);
    map.addControl(new OpenLayers.Control.LayerSwitcher());
    map.setCenter(new OpenLayers.LonLat(13237081.853,3778646.925), 17);
    map.events.register("click",map,function(e){
        var lonlat = map.getLonLatFromViewPortPx(e.xy);
	if(MouseClickArr.length){
            MouseClickArr[MouseClickArr.length-1][4] = lonlat.lon;
            MouseClickArr[MouseClickArr.length-1][5] = lonlat.lat;
        }

        vectorLayer.removeAllFeatures();
        drawFOV();
        drawCross();
    });
}

function drawCross(){
        var features = [];
        for(i=0;i<MouseClickArr.length;i++){
            var point = new OpenLayers.Geometry.Point(MouseClickArr[i][4], MouseClickArr[i][5]);
            var pf3 = new OpenLayers.Feature.Vector(point,null,style_mark);
            features.push(pf3);
        }
        if(features.length){
            vectorLayer.addFeatures(features);
        }
}

function drawFOV(){
    if(!polygon.length) return;
    var pointList = [];
    for(var p=0; p<polygon.length; ++p) {
        var newPoint = new OpenLayers.Geometry.Point(polygon[p][0] , polygon[p][1]);
        pointList.push(newPoint);
    }

    var linearRing = new OpenLayers.Geometry.LinearRing(pointList);
    var polygonFeature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Polygon([linearRing]));
    
    var style_blue = OpenLayers.Util.extend({}, layer_style);
    style_blue.strokeColor = "blue";
    style_blue.fillColor = "blue";
    //style_blue.graphicName = "star";
    style_blue.pointRadius = 1;
    style_blue.strokeWidth = 3;
    style_blue.rotation = 45;
    style_blue.strokeLinecap = "butt";
    var newPoint = new OpenLayers.Geometry.Point(cameraL[0] , cameraL[1]);
    var pointFeature = new OpenLayers.Feature.Vector(newPoint,null,style_blue);

    vectorLayer.addFeatures([polygonFeature,pointFeature]);
}

function send(){
    if(!is_image_loaded){
        alert("请载入图像!");
        return ;
    }
    if(MouseClickArr.length<3){
        alert("至少选择三对点!");
        return ;
    }
    if((isNaN($("#ccd1").val())||isNaN($("#ccd2").val())||isNaN($("#f").val())||isNaN($("#maxl").val())
        ||isNaN($("#maxh").val())||isNaN($("#minh").val())||isNaN($("#maxt").val())||isNaN($("#mint").val()))){
        alert("所有输入框需填写，且为数字");
        return ;
    }
    var i;
    var textP = "[";
    var textL = "[";
    for(i=0;i<MouseClickArr.length;i++){
        if(i>0){
            textP += ",";
            textL += ",";
        }
        textP += "["+MouseClickArr[i][2]+","+MouseClickArr[i][3]+"]";
        textL += "["+MouseClickArr[i][4]+","+MouseClickArr[i][5]+"]";
    }
    textP += "]";
    textL += "]";
    $.ajax({
        type: "POST",
        url: "./json/test.php",
        data: {json: '{"ccd_w":'+$("#ccd1").val()+', "ccd_h":'+$("#ccd2").val()+', "f":'+$("#f").val()+', "H_from":'+$("#minh").val()+', "H_to":'+$("#maxh").val()+', "T_from":'+$("#mint").val()+', "T_to":'+$("#maxt").val()+' ,"maxLength":'+$("#maxl").val()+',"P":'+textP+', "L":'+textL+'}'},
        dataType: "json",
        success: function(data){
            if("error" in data){
                alert("数据错误");
                return;
            }
            $("#info2").html("高度："+data.location[2]+"<br />"+"俯角:"+data.tilt/Math.PI*180+"水平角："+data.pan/Math.PI*180);
            polygon = data.polygon;
            cameraL = [data.location[0],data.location[1]];
            cameraH = data.location[2];
            cameraP = data.pan/Math.PI*180;
            cameraT = data.tilt/Math.PI*180;
            vectorLayer.removeAllFeatures();
            drawFOV();
            drawCross();
        },
        error:  function(XMLHttpRequest, textStatus, errorThrown){ 
            alert("查询失败");
        }
    });
}

function save()
{
    if(cameraH<0)
        return;

    window.location="docalCamera.php?id="+$("#row_id").val()+"&x="+cameraL[0]+"&y="+cameraL[1]+"&h="+cameraH+"&p="+cameraP+"&t="+cameraT+"&ccd1="+$("#ccd1").val()+"&ccd2="+$("#ccd2").val()+"&f="+$("#f").val();
}
