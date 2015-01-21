// variables
var canvas, ctx;
var image;
var iMouseX, iMouseY = 1;
var bMouseDown = false;
var iZoomRadius = 100;
var iZoomPower = 2;

// drawing functions
function clear() { // clear canvas function
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
}

function drawScene() { // main drawScene function
    clear(); // clear canvas

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
    // loading source image
    image = new Image();
    image.onload = function () {
    }
    image.src = 'images/image.jpg';

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
    });

    setInterval(drawScene, 30); // loop drawScene
});