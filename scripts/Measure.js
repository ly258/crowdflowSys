/********************************************************************************************************
Measure.js
基于openlayers的视频几何量测库
create by liuyang
2014/12/10
*********************************************************************************************************/
//
OpenLayers.Measure = OpenLayers.Class({
	id:null,
	i:0,
	j:0,
	k:0,
	canvas:null,
	ctx:null,
	canvasTemp:null,
	ctxTemp:null,
	startX:null,
	startY:null,
	stX:null,
	stY:null,
	endX:null,
	endY:null,
	canvasoffset:null,
	offx:null,
	offy:null,
	isDraw:false,
	ccd_width:null,
	ccd_height:null,
    starttime:null,
    currenttime:null,

	initialize:function(id){		
		this.id=id;		
	},
	
	StartMeasure:function(Vid,fatherElement){
		Vid.pause();
		$("#StartMeasure").attr("disabled","disabled");
		$("#PointCalculation").removeAttr("disabled");
		$("#DistCalculation").removeAttr("disabled");
		$("#SpacePosCal").removeAttr("disabled");
		$("#HeightCalculation").removeAttr("disabled");
		$("#SpaceHeiCal").removeAttr("disabled");
		$("#StopMeasure").removeAttr("disabled");

		var url="doMeasureAction.php?act=obtainvideosize";
		var data={"id":this.id};
		var MeasureObj=this;
		$.getJSON(url,data,function(res){
			MeasureObj.ccd_width=res.ccd_width;
			MeasureObj.ccd_height=res.ccd_height;
			MeasureObj.starttime=res.starttime;

			var a=MeasureObj.ccd_width/MeasureObj.ccd_height;
			var videoheight=$(Vid).width()/a;

			var canvasContainer=document.createElement("div");			
			MeasureObj.canvasTemp=document.createElement("canvas");
			MeasureObj.canvas=document.createElement("canvas");
			canvasContainer.id="canvasContainer";
			MeasureObj.canvasTemp.id="canvasTemp";
			MeasureObj.canvas.id="canvas";			
			
			canvasContainer.setAttribute("width",Vid.width);			
			canvasContainer.setAttribute("float","left");
			
			MeasureObj.canvas.width=$(Vid).width();
			MeasureObj.canvas.height=videoheight;
			MeasureObj.canvasTemp.width=$(Vid).width();
			MeasureObj.canvasTemp.height=videoheight;
			MeasureObj.canvas.setAttribute("style","position:absolute;top:25px;left: auto;");
			MeasureObj.canvasTemp.setAttribute("style","position:absolute;top:25px;left: auto;");

			fatherElement.appendChild(canvasContainer);
			canvasContainer.appendChild(MeasureObj.canvasTemp);
			canvasContainer.appendChild(MeasureObj.canvas);		
			

			MeasureObj.ctx = MeasureObj.canvas.getContext("2d");
			MeasureObj.ctxTemp = MeasureObj.canvasTemp.getContext("2d");
			MeasureObj.canvasoffset=$("#canvas").offset();
			MeasureObj.offx=MeasureObj.canvasoffset.left;
			MeasureObj.offy=MeasureObj.canvasoffset.top;
			
			$("#canvas").mousedown(function(e){
				MeasureObj.handleMouseDown(e);
			});
			$("#canvas").mousemove(function(e){
			    MeasureObj.handleMouseMove(e);
			});
			$("#canvas").mouseup(function(e){
			    MeasureObj.handleMouseUp(e);
			});
			});
	},
	
	StopMeasure:function(Vid,fatherElement){
		Vid.play();
		$("#StartMeasure").removeAttr("disabled");
		$("#PointCalculation").attr("disabled","disabled");
		$("#DistCalculation").attr("disabled","disabled");
		$("#SpacePosCal").attr("disabled","disabled");
		$("#HeightCalculation").attr("disabled","disabled");
		$("#SpaceHeiCal").attr("disabled","disabled");
		$("#StopMeasure").attr("disabled","disabled");
		$("canvasContainer").remove();
		$("canvas").remove();
		$("canvasTemp").remove();
	},

	drawPoint:function(X,Y,context){
		context.beginPath();
		context.arc(X,Y,3,0,Math.PI*2,true);
		context.fillStyle='red';
		context.strokeStyle='red';
		context.stroke();
		context.fill();
		context.closePath();
	},

	drawLine:function(toX,toY,context){
		context.beginPath();
		context.moveTo(this.startX,this.startY);
		context.lineTo(toX,toY);
		context.strokeStyle='red';
		context.stroke();
		context.closePath();
	},

	mouseCoordinate:function(e){
		e.preventDefault();
		e.stopPropagation();
		this.mouseX=parseInt(e.offsetX);
		this.mouseY=parseInt(e.offsetY);
	},
	
	drawText:function(ctx,text,x,y,fontstyle,color){
		ctx.font=fontstyle;
		ctx.fillStyle=color;
		ctx.fillText(text,x,y);
	},
	
	currentTime:function(time)
	{
		this.currenttime=this.starttime+time;
	},

	handleMouseDown:function(e){
		e.stopPropagation();
		if(this.i==1)
			{
				this.mouseCoordinate(e);
				this.drawPoint(this.mouseX,this.mouseY,this.ctx);
				var MeasureObj=this;

				$.ajax({
					url:"doMeasureAction.php?act=SpacePosCal",
					data:{x:this.mouseX,y:this.mouseY,vid:this.id,curtime:this.currenttime,ccd_width:this.ccd_width,ccd_height:this.ccd_height}, 
					type:"post",
					dataType:"json",
					success:function(data){
						//var jsonObject=eval("("+data+")");
						MeasureObj.drawText(MeasureObj.ctx,"("+data.coorx+","+data.coory+")",MeasureObj.mouseX,MeasureObj.mouseY,"10px serif","#FF0000");
					},
					error:function(){
						alert("请求异常！");
					},
				});
			}else

			if(this.i==2||this.i==3||this.i==4||(this.i==5&&this.k==0))
			{
				this.mouseCoordinate(e);

				this.isDraw=true;
				this.startX=this.mouseX;
				this.startY=this.mouseY;
			}else

			if(this.i==5&&this.k==1)
			{
				this.isDraw=true;
			}
	},

	handleMouseMove:function(e){
		$("#canvas").css("cursor","crosshair");
		e.stopPropagation();
		if(this.i==2||this.i==3||this.i==4||this.i==5)
			{
				if(!this.isDraw)
					return;
				this.mouseCoordinate(e);

				this.ctxTemp.clearRect(0, 0, this.canvasTemp.width, this.canvasTemp.height);
				this.drawLine(this.mouseX,this.mouseY,this.ctxTemp);
			}
	},

	handleMouseUp:function(e){
		e.stopPropagation();
		switch(this.i)
		{
			case 2:
				if(!this.isDraw)
					return;
				this.isDraw = false;
			    this.mouseCoordinate(e);
			    
			    this.drawLine(this.mouseX, this.mouseY, this.ctx);
			    var MeasureObj=this;

			    $.ajax({
			    	url:"doMeasureAction.php?act=DistanceCal",
			    	data:{px1:this.startX,py1:this.startY,px2:this.mouseX,py2:this.mouseY,vid:this.id,curtime:this.currenttime,ccd_width:this.ccd_width,ccd_height:this.ccd_height},
			    	type:"post",
			    	dataType:"json",
			    	success:function(data){
			    		MeasureObj.drawText(MeasureObj.ctx,data,(MeasureObj.startX+MeasureObj.mouseX)/2,(MeasureObj.startY+MeasureObj.mouseY)/2-5,"10px serif","#FF0000");
			    	},
			    	error:function(){
						alert("请求异常！");
					},
			    });
			    break;
			case 3:
				if(this.j==0)
				{
					if(!this.isDraw)
						return;
					this.isDraw = false;
				    this.mouseCoordinate(e);
				    
				    this.drawLine(this.mouseX, this.mouseY, this.ctx);
				    this.stX=this.startX;
				    this.stY=this.startY;
				    this.endX=this.mouseX;
				    this.endY=this.mouseY;
				    this.j=1;
				}else if(this.j==1)
				{
					if(!this.isDraw)
						return;
					this.isDraw = false;
				    this.mouseCoordinate(e);
				    
				    this.drawLine(this.mouseX, this.mouseY, this.ctx);

				    this.drawLine(this.stX,this.stY,this.ctx);
				    this.j=0;
				    var MeasureObj=this;

				    $.ajax({
				    	url:"doMeasureAction.php?act=SpaceDistanceCal",
				    	data:{tpx1:this.stX,tpy1:this.stY,bpx1:this.endX,bpy1:this.endY,tpx2:this.startX,tpy2:this.startY,bpx2:this.mouseX,bpy2:this.mouseY,vid:this.id,curtime:this.currenttime,ccd_width:this.ccd_width,ccd_height:this.ccd_height},
				    	type:"post",
				    	dataType:"json",
				    	success:function(data){
				    		MeasureObj.drawText(MeasureObj.ctx,data,(MeasureObj.stX+MeasureObj.startX)/2-10,(MeasureObj.stY+MeasureObj.startY)/2-5,"10px serif","#FF0000");
				    	},
				    	error:function(){
							alert("请求异常！");
						},
				    });
				}
				break;
			case 4:
				if(!this.isDraw)
					return;
				this.isDraw = false;
			    this.mouseCoordinate(e);
			    
			    this.drawLine(this.mouseX, this.mouseY, this.ctx);
			    var MeasureObj=this;

			    $.ajax({
			    	url:"doMeasureAction.php?act=HeightCal",
			    	data:{tpx2:this.startX,tpy2:this.startY,bpx1:this.mouseX,bpy1:this.mouseY,vid:this.id,curtime:this.currenttime,ccd_width:this.ccd_width,ccd_height:this.ccd_height},
			    	type:"post",
			    	dataType:"json",
			    	success:function(data){
			    		MeasureObj.drawText(MeasureObj.ctx,data,(MeasureObj.startX+MeasureObj.mouseX)/2+2,(MeasureObj.startY+MeasureObj.mouseY)/2,"10px serif","#FF0000");
			    	},
			    	error:function(){
						alert("请求异常！");
					},
			    });
			    break;
			case 5:
				if(this.k==0)
				{
					if(!this.isDraw)
						return;
					this.isDraw = false;
				    this.mouseCoordinate(e);
				    this.endX=this.mouseX;
				    this.endY=this.mouseY;
				    
				    this.drawLine(this.mouseX, this.mouseY, this.ctx);
				    this.k=1;
				}else if(this.k==1)
				{
					this.isDraw = false;
					this.ctxTemp.clearRect(0, 0, this.canvasTemp.width, this.canvasTemp.height);
					this.drawLine(this.endX, this.endY, this.ctx);
					this.k=0;
					var MeasureObj=this;

				    $.ajax({
				    	url:"doMeasureAction.php?act=SpaceHeiCal",
				    	data:{tpx2:this.startX,tpy2:this.startY,bpx1:this.endX,bpy1:this.endY,bpx:this.mouseX,bpy:this.mouseY,vid:this.id,curtime:this.currenttime,ccd_width:this.ccd_width,ccd_height:this.ccd_height},
				    	type:"post",
				    	dataType:"json",
				    	success:function(data){
				    		MeasureObj.drawText(MeasureObj.ctx,data,(MeasureObj.startX+MeasureObj.endX)/2+4,(MeasureObj.startY+MeasureObj.endY)/2,"10px serif","#FF0000");
				    	},
				    	error:function(){
							alert("请求异常！");
						},
				    });
				}
				break;
		}
	},

	draw:function(i,time){
		switch(i)
		{
			case 1:
				this.i=1;
				this.currentTime(time);
				break;
			case 2:
				this.i=2;
				this.currentTime(time);
				break;
			case 3:
				this.i=3;
				this.currentTime(time);
				break;
			case 4:
				this.i=4;
				this.currentTime(time);
				break;
			case 5:
				this.i=5;
				this.currentTime(time);
				break;
			default:
				this.i=0;
				break;
		}		
	},
})