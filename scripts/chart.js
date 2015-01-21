/********************************************************************************************************
chart.js
动态图表类
created by liuyang
2015/01/08
*********************************************************************************************************/
//
var dynamicChart = function(){
    
}

dynamicChart.prototype = {
    chart:new Object(),
	setHighcharts : function(){
		Highcharts.setOptions({                                                     
			global: {                                                               
			    useUTC: false                                                       
			}
		});
	},

    addTotalNumPoint : function(data){
        x = (new Date()).getTime();
        this.chart.series[0].addPoint([x, data], true, true);
    },

    addPoint : function(data){
        this.chart.yAxis[0].removePlotLine(2); 
        x = (new Date()).getTime();
        if(data.number > data.alarm)
        {
            this.chart.series[0].addPoint([x,data.number],true,true);
            this.chart.series[0].points[19].update({color:"red"});
        }else{
            this.chart.series[0].addPoint([x, data.number], true, true);
        }
        this.chart.yAxis[0].addPlotLine({
                    id: 2,
                    value: data.alarm,
                    width: 2,
                    dashStyle: "Solid",
                    color: "red",
                    zIndex: 10,
        }); 
    },

	loadchart : function(divname){
        this.chart = new Highcharts.Chart({
            chart: { 
                renderTo: divname,                                                               
                type: 'spline',                                                     
                animation: Highcharts.svg, // don't animate in old IE               
                marginRight: 10,                                                                                                                       
            },                                                                      
            title: {                                                                
                text: ''                                            
            },                                                               
            xAxis: {                                                                
                type: 'datetime',                                                   
                tickPixelInterval: 100                                              
            },                                                                      
            yAxis: {  
                min:0,                                                            
                title: {                                                            
                    text: '人数'                                                   
                },                                                                  
                plotLines: [{                                                       
                    value: 0,                                                       
                    width: 1,                                                       
                    color: '#808080'                                                
                }]                                                                  
            },                                                                      
            tooltip: {                                                              
                formatter: function() {                                             
                        return '<b>'+ this.series.name +'</b><br>'+                
                        Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +'<br>'+
                        Highcharts.numberFormat(this.y, 0);//0表示精确到小数点后0位                          
                }                                                                   
            },                                                                      
            legend: {                                                               
                enabled: false                                                      
            },                                                                      
            exporting: {                                                            
                enabled: false                                                      
            },                                                                      
            series: [{                                                              
                name: '当前人数',                                                
                data: (function() {                                                 
                    // generate an array of random data                             
                    var data = [],                                                  
                        time = (new Date()).getTime(),                              
                        i;                                                                                                                                             
                    for (i = -19; i <= 0; i++) {                                    
                        data.push({                                                 
                            x: time + i * 1000,                                     
                            y: 0,                                       
                        });                                                         
                    }                                                               
                    return data;                                                     
                })()                                                                
            }]
        });                                                                                                                                              
	},
}