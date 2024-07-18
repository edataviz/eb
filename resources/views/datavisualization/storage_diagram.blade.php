@extends('choke.choke_diagram')
@section("renderChart")
@parent
<script type="text/javascript">
	chartParameter.url = "/storagedisplay/loadchart";
	
	editBox.calculateContrainValue = function (serieIndex,x,series,group){
		var sum = 0;
		$.each(series, function( index, value ) {
			if(serieIndex!=index && value.group == group){
				var result = $.grep(value.data, function(e){
					return e.x == x;
				});
				if (result.length > 0 && typeof result[0].y != "undefined" ){
					sum+=result[0].y;
				}
			}
         });
        return sum;
	};

	editBox.genDiagram = function (diagram,view){
		if(typeof diagram == "undefined") return;
		var series 		= diagram.series;
		$.each(series, function( serieIndex, value ) {
			$.each(value.data, function( index, data ) {
				day	= getJsDate(data.D);
				pvalue = parseFloat(data.V);
				pvalue	= isNaN(pvalue)?null:pvalue;
				value.data[index]	= {
										x				: day,
										y				: pvalue,
										extraTooltip	: data.E
									};
	         });
	         //tooltips
	         if(typeof value.extraTooltip == "object" && value.extraTooltip.length>0)
		         value.extraTooltip = value.extraTooltip.join("<br>");
	         else 
		         value.extraTooltip = "";
         });

		/* $.each(series, function( serieIndex, value ) {
			if(value.isAdditional==true||value.isAdditional=="true"){
				var preValue;
				$.each(value.data, function( index, data ) {
					var tmpValue	= editBox.calculateContrainValue(serieIndex,data.x,series,value.group);
					if(typeof preValue=="undefined"){
						value.data[index].y	+=tmpValue;
					}
					else{
						value.data[index].y	=preValue	+ tmpValue;
					}
					preValue		= value.data[index].y;
		         });
			}
         }); */

		$.each(diagram.plotLines, function( index, value ) {
			pvalue = parseFloat(value.value);
			pvalue	= isNaN(pvalue)?null:pvalue;
			value.value	= pvalue;
			value.width		= 2;
         });

		var diagramOption	= {
				chart: {
		            zoomType		: 'xy',
		            backgroundColor	: diagram.bgcolor,
		        },
				credits: false,
		        title: {
		            text: diagram.title,
					style: {
						fontWeight:"bold"
					}
		        },
		        subtitle: {
		            text: null
		        },
		         tooltip: {
		            formatter: function () {
		            	var extraTooltip = (this.point.extraTooltip!="-1"&&this.point.extraTooltip!=-1)?this.point.extraTooltip:this.series.userOptions.extraTooltip;
		            	extraTooltip = typeof extraTooltip == "undefined"?"":extraTooltip;
		            	var point = "<br>"+Highcharts.dateFormat('%b %e', this.x) +': '+this.y;
		                return '<b>' + this.series.name + '</b><br>' + extraTooltip+point;
		                
		            },
	        	}, 
	        
		       /* tooltip: {
		            headerFormat: function () {
	// 		            '<b>{series.name}</b><br>{series.userOptions.extraTooltip}<br>';
						var extraTooltip = (point.extraTooltip!="-1"&&point.extraTooltip!=-1)?this.series.userOptions.extraTooltip:point.extraTooltip;
		                return '<b>' + this.series.name + '</b>' + extraTooltip;
		            },
// 	            '<b>{series.name}</b><br>{point.extraTooltip}<br>',
		            pointFormat: '{point.x:%e. %b}: {point.y:.2f}'
		        }, */
		        exporting: {
		            sourceWidth: view.width(),
		            sourceHeight:view.height(),
		            scale: 1,
		            chartOptions: {
		                subtitle: null
		            }
		        },
        		plotOptions: {
                    series: {
                        marker: {
                            enabled: true,
        					symbol:"circle",
        					radius : 3,
                        }
                    },
                    spline: {
                        marker: {
                            enabled: true
                        }
                    },
        			column: {
                        stacking: 'normal'
                    }
                },
        		series: series,
                xAxis: {
                    type: 'datetime',
                    dateTimeLabelFormats: { // don't display the dummy year
                        month: '%e. %b',
                        year: '%b'
                    },
                    title: {
                        text: 'Occur date',
                        style: {
                            fontWeight:"bold"
                        }
                    }
                },
                yAxis: { // Primary yAxis
//                 	minRange	: minRange,
                	max			: diagram.maxY,
                    min			: diagram.minY,
                    labels: {
                        format: '{value}',
                    },
                    plotLines	: diagram.plotLines,
                    title: {
                        text: diagram.ycaption,
                        style: {
                            fontWeight:"bold"
                        }
                    },
                    opposite: false,
        			endOnTick: false,
                }
        };
		view.highcharts(diagramOption);
	}
</script>
@stop